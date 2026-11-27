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
        $frozen = self::frozenMarginForLineItem($item);

        if ($frozen !== null) {
            return $frozen;
        }

        return match ($item->type) {
            CommercialQuoteLineItemType::Product => CommercialQuoteProductMarginCalculator::forLineItem($item),
            CommercialQuoteLineItemType::Personnel => CommercialQuotePersonnelMarginCalculator::forLineItem($item),
            CommercialQuoteLineItemType::Contract => CommercialQuoteContractMarginCalculator::forLineItem($item),
            default => [
                'default_customer_cost' => 0.0,
                'default_customer_amount' => 0.0,
                'default_margin_percent' => 0.0,
                'default_margin_amount' => 0.0,
            ],
        };
    }

    /**
     * @return array<string, mixed>|null
     */
    protected static function frozenMarginForLineItem(CommercialQuoteLineItem $item): ?array
    {
        $item->loadMissing('group.quote');

        $quote = $item->group?->quote;

        if (! $quote?->isEditingLocked()) {
            return null;
        }

        $lines = $quote->frozen_snapshot['margin_lines'] ?? null;

        if (! is_array($lines)) {
            return null;
        }

        foreach ($lines as $line) {
            if ((int) ($line['line_item_id'] ?? 0) === (int) $item->getKey()) {
                $margin = $line['margin'] ?? null;

                return is_array($margin) ? $margin : null;
            }
        }

        return null;
    }
}
