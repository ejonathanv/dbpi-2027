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
            ->where('type', CommercialQuoteLineItemType::Product)
            ->with('additionalCosts')
            ->get();

        $customerAmount = 0.0;
        $marginAmount = 0.0;

        foreach ($lineItems as $item) {
            $margin = CommercialQuoteProductMarginCalculator::forLineItem($item);
            $customerAmount += (float) $margin['customer_amount'];
            $marginAmount += (float) $margin['margin_amount'];
        }

        $marginPercent = $customerAmount > 0
            ? ($marginAmount / $customerAmount) * 100
            : 0.0;

        return [
            'responsible_name' => (string) ($quote->creator?->name ?: '—'),
            'customer_amount' => $customerAmount,
            'margin_percent' => $marginPercent,
            'margin_amount' => $marginAmount,
        ];
    }
}
