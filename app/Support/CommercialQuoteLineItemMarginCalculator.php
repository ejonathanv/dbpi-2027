<?php

namespace App\Support;

use App\Enums\CommercialQuoteLineItemType;
use App\Models\CommercialQuoteLineItem;

class CommercialQuoteLineItemMarginCalculator
{
    /**
     * @return array<string, mixed>
     */
    public static function forLineItem(CommercialQuoteLineItem $item): array
    {
        return match ($item->type) {
            CommercialQuoteLineItemType::Product => CommercialQuoteProductMarginCalculator::forLineItem($item),
            CommercialQuoteLineItemType::Personnel => CommercialQuotePersonnelMarginCalculator::forLineItem($item),
            default => [
                'default_customer_cost' => 0.0,
                'default_customer_amount' => 0.0,
                'default_margin_percent' => 0.0,
                'default_margin_amount' => 0.0,
            ],
        };
    }
}
