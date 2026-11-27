<?php

namespace App\Support;

use App\Models\CommercialFup;
use App\Models\CommercialQuote;
use App\Models\CrmAccount;
use App\Models\SystemSetting;
use Illuminate\Support\Carbon;

class CommercialFupCoverBuilder
{
    /**
     * @return array<string, mixed>
     */
    public static function forFup(CommercialFup $fup): array
    {
        if ($fup->isEditingLocked() && filled($fup->frozen_snapshot['cover'] ?? null)) {
            return (array) $fup->frozen_snapshot['cover'];
        }

        $fup->loadMissing([
            'quote.account',
            'quote.contact',
            'quote.opportunity',
            'accountExecutive',
        ]);

        $quote = $fup->quote;
        $account = $quote?->account;
        $contact = $quote?->contact;

        $shipping = self::addressParts($account?->delivery_address);
        $billing = self::addressParts($account?->billing_address);

        return [
            'issuer_logo_url' => asset('img/svg/logo-dynacom.svg'),
            'fup_number' => $fup->fup_number,
            'quote_number' => (string) ($quote?->quote_number ?? '—'),
            'client_po_number' => (string) ($fup->client_po_number ?? '—'),
            'fup_date' => self::formatHeaderDate($fup->fup_date),
            'trade_name' => (string) ($account?->name ?? '—'),
            'project_description' => (string) ($quote?->project_description ?? '—'),
            'technical_contact_name' => $contact?->fullName() ?? '—',
            'technical_contact_email' => (string) ($contact?->email ?? '—'),
            'technical_contact_phone' => (string) ($contact?->phone ?? '—'),
            'shipping' => [
                'legal_name' => (string) ($account?->legal_name ?? $account?->name ?? '—'),
                'street' => $shipping['street'],
                'city_state' => $shipping['city_state'],
                'postal_code' => $shipping['postal_code'],
                'partial_deliveries' => ($account?->allows_partial_deliveries ?? false) ? '(SI)' : '(NO)',
                'delivery_days' => (string) ($account?->delivery_days ?? '—'),
                'delivery_schedule' => (string) ($account?->delivery_schedule ?? '—'),
                'references' => (string) ($account?->general_description ?? '—'),
            ],
            'billing' => [
                'legal_name' => (string) ($account?->legal_name ?? $account?->name ?? '—'),
                'street' => $billing['street'],
                'city_state' => $billing['city_state'],
                'postal_code' => $billing['postal_code'],
                'rfc' => self::resolveAccountRfc($account),
                'mub_percent' => $account?->institutional_discount_percent !== null
                    ? number_format((float) $account->institutional_discount_percent, 2).'%'
                    : '—',
                'currency_code' => $quote?->presentation_currency?->value ?? 'USD',
                'exchange_rate' => number_format((float) ($quote?->exchange_rate ?? 0), 3, '.', ','),
            ],
            'payment_rows' => self::paymentRows($account),
            'order_info' => [
                'delivery_time' => (string) ($quote?->delivery_time ?? '—'),
                'commitment_delivery_date' => self::formatCommitmentDate($fup->commitment_delivery_date),
                'commitment_installation_start_date' => self::formatCommitmentDate($fup->commitment_installation_start_date),
                'commitment_project_date' => self::formatCommitmentDate($fup->commitment_project_date),
            ],
            'final_client' => [
                'name' => (string) ($account?->legal_name ?? $account?->name ?? '—'),
                'street' => $shipping['street'],
                'account_executive' => (string) ($fup->accountExecutive?->name ?? '—'),
                'city_state' => $shipping['city_state'],
                'postal_code' => $shipping['postal_code'],
            ],
            'fiscal_line' => self::fiscalLine($account),
            'administration_notes' => (string) ($account?->accounts_payable_info ?? ''),
            'observations_and_recommendations' => trim(strip_tags((string) ($fup->observations_and_recommendations ?? ''))),
            'footer_legal' => (string) (
                SystemSetting::get('commercial_quote_footer_legal')
                ?? 'DYNACOM- FPF-02 REV 2.3 NOV 1999 AAJR.'
            ),
            'footer_confidential' => 'CONFIDENTIAL DYNAMIC COMMUNICATIONS PROPRIETARY',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function capture(CommercialFup $fup): array
    {
        $fup->loadMissing('quote');

        return [
            'captured_at' => now()->toIso8601String(),
            'cover' => self::forFup($fup),
            'quote_id' => $fup->commercial_quote_id,
            'quote_frozen' => $fup->quote instanceof CommercialQuote
                ? ($fup->quote->frozen_snapshot ?? CommercialQuoteFreezeSupport::capture($fup->quote))
                : null,
        ];
    }

    /**
     * @return array{street: string, city_state: string, postal_code: string}
     */
    protected static function addressParts(?string $address): array
    {
        if (blank($address)) {
            return [
                'street' => '—',
                'city_state' => '—',
                'postal_code' => '—',
            ];
        }

        $normalized = trim($address);
        $lines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $normalized) ?: [])));

        $street = $lines[0] ?? $normalized;
        $cityState = $lines[1] ?? ($lines[2] ?? '—');

        if (count($lines) > 2) {
            $cityState = trim($lines[1].', '.$lines[2]);
        }

        $postalCode = '—';

        if (preg_match('/\b(\d{5}(?:-\d{4})?)\b/', $normalized, $matches)) {
            $postalCode = $matches[1];
        }

        return [
            'street' => $street,
            'city_state' => $cityState !== '' ? $cityState : '—',
            'postal_code' => $postalCode,
        ];
    }

    /**
     * @return list<array{concept: string, percent: string, days: string}>
     */
    protected static function paymentRows(?CrmAccount $account): array
    {
        if (! $account) {
            return [
                ['concept' => 'ANTICIPO', 'percent' => '—', 'days' => '—'],
                ['concept' => 'ENTREGA', 'percent' => '—', 'days' => '—'],
                ['concept' => 'INSTALACION', 'percent' => '—', 'days' => '—'],
            ];
        }

        return [
            [
                'concept' => 'ANTICIPO',
                'percent' => self::formatPercent($account->advance_percent),
                'days' => self::formatDays($account->advance_days),
            ],
            [
                'concept' => 'ENTREGA',
                'percent' => self::formatPercent($account->on_delivery_percent),
                'days' => self::formatDays($account->on_delivery_days),
            ],
            [
                'concept' => 'INSTALACION',
                'percent' => self::formatPercent($account->installation_percent),
                'days' => self::formatDays($account->installation_days),
            ],
        ];
    }

    protected static function formatPercent(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        return number_format((float) $value, 0, '.', ',');
    }

    protected static function formatDays(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        return (string) (int) $value;
    }

    protected static function resolveAccountRfc(?CrmAccount $account): string
    {
        if (! $account) {
            return '—';
        }

        $candidates = [
            (string) ($account->sic_code ?? ''),
            (string) ($account->vat_treatment ?? ''),
        ];

        foreach ($candidates as $candidate) {
            if (preg_match('/^[A-ZÑ&]{3,4}\d{6}[A-Z0-9]{3}$/i', trim($candidate))) {
                return strtoupper(trim($candidate));
            }
        }

        return '—';
    }

    protected static function fiscalLine(?CrmAccount $account): string
    {
        if (! $account) {
            return '—';
        }

        $parts = array_filter([
            filled($account->payment_method) ? 'METODO DE PAGO: '.$account->payment_method : null,
            filled($account->payment_form) ? 'FORMA DE PAGO: '.$account->payment_form : null,
            filled($account->cfdi_use) ? 'USO DE CFDI: '.$account->cfdi_use : null,
        ]);

        return $parts !== [] ? implode(', ', $parts) : '—';
    }

    protected static function formatHeaderDate(mixed $date): string
    {
        if (! $date instanceof Carbon) {
            return '—';
        }

        return strtoupper($date->locale('es')->translatedFormat('d M, Y'));
    }

    protected static function formatCommitmentDate(mixed $date): string
    {
        if (! $date instanceof Carbon) {
            return 'N/A';
        }

        return strtoupper($date->locale('es')->translatedFormat('j-F-Y'));
    }
}