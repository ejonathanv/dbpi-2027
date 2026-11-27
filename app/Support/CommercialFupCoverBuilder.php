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
    public static function defaultPayload(CommercialFup $fup): array
    {
        $fup->loadMissing([
            'quote.account',
            'quote.contact',
            'accountExecutive',
        ]);

        $quote = $fup->quote;
        $account = $quote?->account;
        $contact = $quote?->contact;

        $shipping = self::addressParts($account?->delivery_address);
        $billing = self::addressParts($account?->billing_address);

        $quoteMarginPercent = $quote
            ? (float) CommercialQuoteTotalsCalculator::forQuote($quote)['margin_percent']
            : 0.0;

        return [
            'trade_name' => (string) ($account?->name ?? ''),
            'project_description' => (string) ($quote?->project_description ?? ''),
            'technical_contact_name' => $contact?->fullName() ?? '',
            'technical_contact_email' => (string) ($contact?->email ?? ''),
            'technical_contact_phone' => (string) ($contact?->phone ?? ''),
            'shipping' => [
                'legal_name' => (string) ($account?->legal_name ?? $account?->name ?? ''),
                'street' => $shipping['street'] === '—' ? '' : $shipping['street'],
                'city_state' => $shipping['city_state'] === '—' ? '' : $shipping['city_state'],
                'postal_code' => $shipping['postal_code'] === '—' ? '' : $shipping['postal_code'],
                'partial_deliveries' => ($account?->allows_partial_deliveries ?? false) ? 'SI' : 'NO',
                'delivery_days' => (string) ($account?->delivery_days ?? ''),
                'delivery_schedule' => (string) ($account?->delivery_schedule ?? ''),
                'references' => (string) ($account?->general_description ?? ''),
            ],
            'billing' => [
                'legal_name' => (string) ($account?->legal_name ?? $account?->name ?? ''),
                'street' => $billing['street'] === '—' ? '' : $billing['street'],
                'city_state' => $billing['city_state'] === '—' ? '' : $billing['city_state'],
                'postal_code' => $billing['postal_code'] === '—' ? '' : $billing['postal_code'],
                'rfc' => ($rfc = self::resolveAccountRfc($account)) === '—' ? '' : $rfc,
                'mub_percent' => number_format($quoteMarginPercent, 2, '.', ''),
                'currency_code' => $quote?->presentation_currency?->value ?? 'USD',
                'exchange_rate' => (string) ($quote?->exchange_rate ?? ''),
            ],
            'payment_rows' => [
                ['concept' => 'ANTICIPO', 'percent' => self::rawPercent($account?->advance_percent), 'days' => self::rawDays($account?->advance_days)],
                ['concept' => 'ENTREGA', 'percent' => self::rawPercent($account?->on_delivery_percent), 'days' => self::rawDays($account?->on_delivery_days)],
                ['concept' => 'INSTALACION', 'percent' => self::rawPercent($account?->installation_percent), 'days' => self::rawDays($account?->installation_days)],
            ],
            'order_info' => [
                'delivery_time' => (string) ($quote?->delivery_time ?? ''),
            ],
            'final_client' => [
                'name' => (string) ($account?->legal_name ?? $account?->name ?? ''),
                'street' => $shipping['street'] === '—' ? '' : $shipping['street'],
                'city_state' => $shipping['city_state'] === '—' ? '' : $shipping['city_state'],
                'postal_code' => $shipping['postal_code'] === '—' ? '' : $shipping['postal_code'],
            ],
            'fiscal' => [
                'payment_method' => (string) ($account?->payment_method ?? ''),
                'payment_form' => (string) ($account?->payment_form ?? ''),
                'cfdi_use' => (string) ($account?->cfdi_use ?? ''),
            ],
            'administration_notes' => (string) ($account?->accounts_payable_info ?? ''),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function mergeCoverData(CommercialFup $fup): array
    {
        $defaults = self::defaultPayload($fup);
        $stored = is_array($fup->cover_payload) ? $fup->cover_payload : [];

        return array_replace_recursive($defaults, $stored);
    }

    /**
     * @return array<string, mixed>
     */
    public static function forFup(CommercialFup $fup): array
    {
        if ($fup->isEditingLocked() && filled($fup->frozen_snapshot['cover'] ?? null)) {
            return (array) $fup->frozen_snapshot['cover'];
        }

        $fup->loadMissing(['quote', 'accountExecutive']);

        return self::presentCover(self::mergeCoverData($fup), $fup);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected static function presentCover(array $data, CommercialFup $fup): array
    {
        $quote = $fup->quote;
        $shipping = $data['shipping'] ?? [];
        $billing = $data['billing'] ?? [];
        $paymentRows = $data['payment_rows'] ?? [];

        foreach ($paymentRows as $index => $row) {
            $paymentRows[$index]['percent'] = filled($row['percent'] ?? null)
                ? (string) $row['percent']
                : '—';
            $paymentRows[$index]['days'] = filled($row['days'] ?? null)
                ? (string) $row['days']
                : '—';
        }

        $partial = (string) ($shipping['partial_deliveries'] ?? '');
        if (filled($partial) && ! str_starts_with($partial, '(')) {
            $partial = '('.$partial.')';
        }

        return [
            'issuer_logo_url' => asset('img/svg/logo-dynacom.svg'),
            'fup_number' => $fup->fup_number,
            'quote_number' => (string) ($quote?->quote_number ?? '—'),
            'client_po_number' => (string) ($fup->client_po_number ?? '—'),
            'fup_date' => self::formatHeaderDate($fup->fup_date),
            'trade_name' => (string) ($data['trade_name'] ?? '—'),
            'project_description' => (string) ($data['project_description'] ?? '—'),
            'technical_contact_name' => (string) ($data['technical_contact_name'] ?? '—'),
            'technical_contact_email' => (string) ($data['technical_contact_email'] ?? '—'),
            'technical_contact_phone' => (string) ($data['technical_contact_phone'] ?? '—'),
            'shipping' => [
                'legal_name' => (string) ($shipping['legal_name'] ?? '—'),
                'street' => (string) ($shipping['street'] ?? '—'),
                'city_state' => (string) ($shipping['city_state'] ?? '—'),
                'postal_code' => (string) ($shipping['postal_code'] ?? '—'),
                'partial_deliveries' => filled($partial) ? $partial : '—',
                'delivery_days' => (string) ($shipping['delivery_days'] ?? '—'),
                'delivery_schedule' => (string) ($shipping['delivery_schedule'] ?? '—'),
                'references' => (string) ($shipping['references'] ?? '—'),
            ],
            'billing' => [
                'legal_name' => (string) ($billing['legal_name'] ?? '—'),
                'street' => (string) ($billing['street'] ?? '—'),
                'city_state' => (string) ($billing['city_state'] ?? '—'),
                'postal_code' => (string) ($billing['postal_code'] ?? '—'),
                'rfc' => (string) ($billing['rfc'] ?? '—'),
                'mub_percent' => filled($billing['mub_percent'] ?? null)
                    ? number_format((float) $billing['mub_percent'], 2).'%'
                    : '—',
                'currency_code' => (string) ($billing['currency_code'] ?? '—'),
                'exchange_rate' => filled($billing['exchange_rate'] ?? null)
                    ? number_format((float) $billing['exchange_rate'], 3, '.', ',')
                    : '—',
            ],
            'payment_rows' => $paymentRows,
            'order_info' => [
                'delivery_time' => (string) (($data['order_info']['delivery_time'] ?? '') ?: '—'),
                'commitment_delivery_date' => self::formatCommitmentDate($fup->commitment_delivery_date),
                'commitment_installation_start_date' => self::formatCommitmentDate($fup->commitment_installation_start_date),
                'commitment_project_date' => self::formatCommitmentDate($fup->commitment_project_date),
            ],
            'final_client' => [
                'name' => (string) (($data['final_client']['name'] ?? '') ?: '—'),
                'street' => (string) (($data['final_client']['street'] ?? '') ?: '—'),
                'account_executive' => (string) ($fup->accountExecutive?->name ?? '—'),
                'city_state' => (string) (($data['final_client']['city_state'] ?? '') ?: '—'),
                'postal_code' => (string) (($data['final_client']['postal_code'] ?? '') ?: '—'),
            ],
            'fiscal_line' => self::fiscalLineFromPayload($data['fiscal'] ?? []),
            'administration_notes' => (string) ($data['administration_notes'] ?? ''),
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

    protected static function rawPercent(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return (string) (float) $value;
    }

    protected static function rawDays(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
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

    /**
     * @param  array<string, mixed>  $fiscal
     */
    protected static function fiscalLineFromPayload(array $fiscal): string
    {
        $parts = array_filter([
            filled($fiscal['payment_method'] ?? null) ? 'METODO DE PAGO: '.$fiscal['payment_method'] : null,
            filled($fiscal['payment_form'] ?? null) ? 'FORMA DE PAGO: '.$fiscal['payment_form'] : null,
            filled($fiscal['cfdi_use'] ?? null) ? 'USO DE CFDI: '.$fiscal['cfdi_use'] : null,
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