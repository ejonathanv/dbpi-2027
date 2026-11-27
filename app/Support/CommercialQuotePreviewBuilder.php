<?php

namespace App\Support;

use App\Enums\CommercialQuoteLineItemType;
use App\Enums\SalaryCurrency;
use App\Models\CommercialQuote;
use App\Models\CommercialQuoteGroup;
use App\Models\CommercialQuoteLineItem;
use App\Models\CrmAccount;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class CommercialQuotePreviewBuilder
{
    /**
     * @return array<string, mixed>
     */
    public static function forQuote(CommercialQuote $quote): array
    {
        $quote->loadMissing([
            'account',
            'contact',
            'groups.lineItems.product',
            'groups.lineItems.personnel',
            'groups.lineItems.contract',
            'groups.lineItems.additionalCosts',
        ]);

        $currency = $quote->presentation_currency ?? SalaryCurrency::Usd;
        $currencyCode = $currency->value;

        /** @var array<int, array<string, mixed>> $rows */
        $rows = [];

        foreach ($quote->groups as $group) {
            if ($group->hide_group_in_quote) {
                continue;
            }

            if ($group->hide_items_in_quote) {
                $collapsed = self::groupCollapsedRow($group, $quote);

                if ($collapsed !== null) {
                    $rows[] = $collapsed;
                }

                continue;
            }

            if (! $group->hide_title_in_quote && filled($group->title)) {
                $rows[] = [
                    'type' => 'group_title',
                    'title' => $group->title,
                ];
            }

            foreach ($group->lineItems as $item) {
                $line = self::lineRow($item, $quote);

                if ($line !== null) {
                    $rows[] = $line;
                }
            }
        }

        $linesSubtotal = collect($rows)
            ->where('type', 'line')
            ->sum(fn (array $row): float => (float) $row['subtotal']);

        $generalDiscountPercent = max((float) $quote->general_discount_percent, 0);
        $generalDiscountAmount = $linesSubtotal * ($generalDiscountPercent / 100);
        $subtotalAfterDiscount = $linesSubtotal - $generalDiscountAmount;

        $vatPercent = max((float) ($quote->vat_percent ?? 0), 0);
        $vatIncluded = (bool) $quote->vat_included;

        $vatAmount = 0.0;
        $subtotalExcludingVat = $subtotalAfterDiscount;
        $taxNote = null;

        if ($vatPercent > 0) {
            if ($vatIncluded) {
                $subtotalExcludingVat = $subtotalAfterDiscount / (1 + ($vatPercent / 100));
                $vatAmount = $subtotalAfterDiscount - $subtotalExcludingVat;
                $total = $subtotalAfterDiscount;
                $taxNote = sprintf(
                    'IVA (%s%%) incluido en los precios',
                    number_format($vatPercent, 2, '.', ','),
                );
            } else {
                $vatAmount = $subtotalAfterDiscount * ($vatPercent / 100);
                $total = $subtotalAfterDiscount + $vatAmount;
                $taxNote = sprintf(
                    'NOTA: IVA (%s%%) no incluido',
                    number_format($vatPercent, 2, '.', ','),
                );
            }
        } else {
            $total = $subtotalAfterDiscount;
        }

        $contact = $quote->contact;
        $account = $quote->account;
        $customerLogoUrl = self::customerLogoUrl($account);

        $validityDays = (int) ($quote->validity_days ?? 0);
        $pricesNote = $currencyCode === 'USD'
            ? sprintf('TODOS LOS PRECIOS EN USD Y VÁLIDOS POR %d DÍAS', $validityDays)
            : sprintf('TODOS LOS PRECIOS EN MXN Y VÁLIDOS POR %d DÍAS', $validityDays);

        return [
            'issuer_logo_url' => self::issuerLogoUrl(),
            'issuer_address_lines' => self::issuerAddressLines(),
            'customer_company' => (string) ($account?->name ?? '—'),
            'customer_logo_url' => $customerLogoUrl,
            'contact_name' => $contact?->fullName() ?: '—',
            'contact_email' => (string) ($contact?->email ?? ''),
            'contact_phone' => (string) ($contact?->phone ?? ''),
            'quoted_at' => $quote->quoted_at?->format('d/m/Y') ?? '—',
            'quote_number' => (string) ($quote->quote_number ?? '—'),
            'payment_terms' => (string) ($quote->payment_terms ?? ''),
            'delivery_time' => (string) ($quote->delivery_time ?? ''),
            'fob' => (string) ($quote->fob ?? ''),
            'currency_code' => $currencyCode,
            'rows' => $rows,
            'lines_subtotal' => $linesSubtotal,
            'general_discount_amount' => $generalDiscountAmount,
            'subtotal' => $subtotalAfterDiscount,
            'subtotal_excluding_vat' => $subtotalExcludingVat,
            'vat_percent' => $vatPercent,
            'vat_included' => $vatIncluded,
            'vat_amount' => $vatAmount,
            'total' => $total,
            'general_discount_percent' => $generalDiscountPercent,
            'tax_note' => $taxNote,
            'observations' => self::plainText($quote->observations),
            'project_description' => self::plainText($quote->project_description),
            'prices_note' => $pricesNote,
            'footer_legal' => (string) (
                SystemSetting::get('commercial_quote_footer_legal')
                ?? 'DYNACOM- FPF-02 REV 2.3 NOV 1999 AAJR.'
            ),
            'footer_confidential' => 'CONFIDENTIAL DYNAMIC COMMUNICATIONS PROPRIETARY',
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    protected static function groupCollapsedRow(CommercialQuoteGroup $group, CommercialQuote $quote): ?array
    {
        $subtotal = 0.0;

        foreach ($group->lineItems as $item) {
            $line = self::lineRow($item, $quote);

            if ($line !== null) {
                $subtotal += (float) $line['subtotal'];
            }
        }

        if ($subtotal <= 0) {
            return null;
        }

        $headline = filled($group->title) ? (string) $group->title : 'Grupo';

        return [
            'type' => 'line',
            'qty' => 1,
            'qty_label' => '1',
            'description_headline' => $headline,
            'description_extra' => '',
            'unit' => $subtotal,
            'subtotal' => $subtotal,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    protected static function lineRow(CommercialQuoteLineItem $item, CommercialQuote $quote): ?array
    {
        $margin = CommercialQuoteLineItemMarginCalculator::forLineItem($item);
        $customerAmount = (float) ($margin['customer_amount'] ?? 0);

        return match ($item->type) {
            CommercialQuoteLineItemType::Product => self::productRow($item, $quote, $customerAmount),
            CommercialQuoteLineItemType::Personnel => self::personnelRow($item, $quote, $customerAmount),
            CommercialQuoteLineItemType::Contract => self::contractRow($item, $quote, $customerAmount),
            default => null,
        };
    }

    /**
     * @return array<string, mixed>|null
     */
    protected static function productRow(CommercialQuoteLineItem $item, CommercialQuote $quote, float $customerAmount): ?array
    {
        if ($item->hide_product_in_quote) {
            return null;
        }

        $qty = max((float) $item->product_quantity, 0);
        if ($qty <= 0) {
            return null;
        }

        $unit = self::toPresentationCurrency($customerAmount / $qty, $quote);
        $subtotal = self::toPresentationCurrency($customerAmount, $quote);

        $product = $item->product;
        $titleParts = [];

        if ($item->show_part_number && filled($product?->part_number)) {
            $titleParts[] = '#'.$product->part_number;
        }

        $titleParts[] = $item->product_alias ?: ($product?->name ?? 'Producto');
        $headline = implode(' - ', array_filter($titleParts));

        $extra = self::plainText($product?->specifications ?? '');

        return [
            'type' => 'line',
            'qty' => $qty,
            'qty_label' => self::formatQty($qty),
            'description_headline' => $headline,
            'description_extra' => $extra,
            'unit' => $unit,
            'subtotal' => $subtotal,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected static function personnelRow(CommercialQuoteLineItem $item, CommercialQuote $quote, float $customerAmount): array
    {
        $qty = max((float) $item->personnel_quantity, 0);
        if ($qty <= 0) {
            $qty = 1;
        }

        $unit = self::toPresentationCurrency($customerAmount / $qty, $quote);
        $subtotal = self::toPresentationCurrency($customerAmount, $quote);

        $name = (string) ($item->personnel?->name ?? 'Recurso');
        $days = (int) $item->personnel_days;
        $extra = $days > 0 ? "{$days} día(s)" : '';

        return [
            'type' => 'line',
            'qty' => $qty,
            'qty_label' => self::formatQty($qty),
            'description_headline' => Str::upper($name),
            'description_extra' => $extra,
            'unit' => $unit,
            'subtotal' => $subtotal,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected static function contractRow(CommercialQuoteLineItem $item, CommercialQuote $quote, float $customerAmount): array
    {
        $subtotal = self::toPresentationCurrency($customerAmount, $quote);
        $name = (string) ($item->contract?->name ?? 'Contrato');
        $validity = $item->contract?->validityLabel() ?? '';
        $description = $item->contract?->description;
        $extra = trim(implode(' · ', array_filter([
            $validity !== '' ? "Vigencia: {$validity}" : null,
            filled($description) ? self::plainText($description) : null,
        ])));

        return [
            'type' => 'line',
            'qty' => 1,
            'qty_label' => '1',
            'description_headline' => $name,
            'description_extra' => $extra,
            'unit' => $subtotal,
            'subtotal' => $subtotal,
        ];
    }

    protected static function toPresentationCurrency(float $amountUsd, CommercialQuote $quote): float
    {
        if (($quote->presentation_currency ?? SalaryCurrency::Usd) === SalaryCurrency::Mxn) {
            return $amountUsd * max((float) $quote->exchange_rate, 0);
        }

        return $amountUsd;
    }

    protected static function formatQty(float $qty): string
    {
        if (abs($qty - round($qty)) < 0.0001) {
            return (string) (int) round($qty);
        }

        return number_format($qty, 2, '.', ',');
    }

    /**
     * @return list<string>
     */
    protected static function issuerAddressLines(): array
    {
        return [
            'CALLE LAZARO CARDENAS 341-C',
            'COL. JARDINES DEL LAGO',
            'MEXICALI, BC.',
        ];
    }

    protected static function issuerLogoUrl(): string
    {
        return asset('img/svg/logo-dynacom.svg');
    }

    protected static function customerLogoUrl(?CrmAccount $account): ?string
    {
        $path = CrmAccount::normalizeLogoPath($account?->logo_path);

        if (blank($path)) {
            return null;
        }

        try {
            if (! Storage::disk('public')->exists($path)) {
                return null;
            }
        } catch (Throwable) {
            return null;
        }

        return '/storage/'.ltrim(str_replace('\\', '/', $path), '/');
    }

    protected static function plainText(?string $html): string
    {
        if (blank($html)) {
            return '';
        }

        return trim(preg_replace('/\s+/', ' ', strip_tags($html)) ?? '');
    }
}
