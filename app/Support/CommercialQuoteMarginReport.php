<?php

namespace App\Support;

use App\Models\CommercialQuote;

class CommercialQuoteMarginReport
{
    /**
     * @return array{
     *     quote_number: string,
     *     quoted_at: string,
     *     exchange_rate: string,
     *     currency_code: string,
     *     groups: list<array{
     *         title: string,
     *         exclude_from_margin: bool,
     *         lines: list<array{
     *             type_label: string,
     *             label: string,
     *             supplier_amount: float,
     *             customer_amount: float,
     *             margin_percent: float,
     *             margin_amount: float,
     *         }>,
     *     }>,
     *     totals: array<string, mixed>,
     * }
     */
    public static function forQuote(CommercialQuote $quote): array
    {
        $quote->loadMissing([
            'groups.lineItems.product',
            'groups.lineItems.personnel',
            'groups.lineItems.contract',
            'account',
        ]);

        $currencyCode = $quote->presentation_currency?->value ?? 'USD';

        $groups = [];

        foreach ($quote->groups as $group) {
            $lines = [];

            foreach ($group->lineItems as $item) {
                $margin = CommercialQuoteLineItemMarginCalculator::forLineItem($item);

                $lines[] = [
                    'type_label' => (string) ($item->type?->getLabel() ?? 'Partida'),
                    'label' => CommercialQuoteFreezeSupport::lineItemLabel($item),
                    'supplier_amount' => self::supplierAmount($margin),
                    'customer_amount' => (float) ($margin['customer_amount'] ?? 0),
                    'margin_percent' => (float) ($margin['margin_percent'] ?? 0),
                    'margin_amount' => (float) ($margin['margin_amount'] ?? 0),
                ];
            }

            $groups[] = [
                'title' => (string) $group->title,
                'exclude_from_margin' => (bool) $group->exclude_from_margin,
                'lines' => $lines,
            ];
        }

        return [
            'quote_number' => (string) ($quote->quote_number ?? '—'),
            'quoted_at' => $quote->quoted_at?->format('d/m/Y') ?? '—',
            'exchange_rate' => number_format((float) $quote->exchange_rate, 6, '.', ','),
            'currency_code' => $currencyCode,
            'groups' => $groups,
            'totals' => self::totalsForQuote($quote),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function totalsForQuote(CommercialQuote $quote): array
    {
        if ($quote->isEditingLocked() && filled($quote->frozen_snapshot['totals'] ?? null)) {
            return (array) $quote->frozen_snapshot['totals'];
        }

        return CommercialQuoteTotalsCalculator::forQuote($quote);
    }

    /**
     * @param  array<string, mixed>  $margin
     */
    protected static function supplierAmount(array $margin): float
    {
        if (array_key_exists('supplier_amount', $margin)) {
            return (float) $margin['supplier_amount'];
        }

        return (float) ($margin['supplier_amount_net'] ?? 0);
    }
}