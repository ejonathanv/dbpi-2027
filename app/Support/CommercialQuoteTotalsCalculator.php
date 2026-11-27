<?php

namespace App\Support;

use App\Enums\CommercialQuoteLineItemType;
use App\Models\CommercialQuote;
use App\Models\CommercialQuoteLineItem;

class CommercialQuoteTotalsCalculator
{
    /**
     * @return array{
     *     responsible_name: string,
     *     customer_amount: float,
     *     margin_percent: float,
     *     margin_amount: float,
     * }
     */
    public static function forQuote(CommercialQuote $quote): array
    {
        $quote->loadMissing('creator');

        $lineItems = CommercialQuoteLineItem::query()
            ->whereHas('group', fn ($query) => $query->where('commercial_quote_id', $quote->getKey()))
            ->whereIn('type', [
                CommercialQuoteLineItemType::Product,
                CommercialQuoteLineItemType::Personnel,
                CommercialQuoteLineItemType::Contract,
            ])
            ->with(['group', 'additionalCosts', 'personnel', 'contract'])
            ->get();

        $customerAmount = 0.0;
        $customerAmountForMargin = 0.0;
        $marginAmount = 0.0;

        foreach ($lineItems as $item) {
            $margin = CommercialQuoteLineItemMarginCalculator::forLineItem($item);
            $lineCustomerAmount = (float) ($margin['customer_amount'] ?? 0);
            $lineMarginAmount = (float) ($margin['margin_amount'] ?? 0);

            $customerAmount += $lineCustomerAmount;

            if ($item->group?->exclude_from_margin) {
                continue;
            }

            $customerAmountForMargin += $lineCustomerAmount;
            $marginAmount += $lineMarginAmount;
        }

        $marginPercent = $customerAmountForMargin > 0
            ? ($marginAmount / $customerAmountForMargin) * 100
            : 0.0;

        return [
            'responsible_name' => (string) ($quote->creator?->name ?: '—'),
            'customer_amount' => $customerAmount,
            'margin_percent' => $marginPercent,
            'margin_amount' => $marginAmount,
        ];
    }
}
