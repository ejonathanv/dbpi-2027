<?php

namespace App\Support;

use App\Enums\CommercialQuoteLineItemType;
use App\Models\CommercialQuote;
use App\Models\CommercialQuoteLineItem;

class CommercialQuoteFreezeSupport
{
    /**
     * @return array<string, mixed>
     */
    public static function capture(CommercialQuote $quote): array
    {
        $quote->loadMissing([
            'groups.lineItems.product',
            'groups.lineItems.personnel',
            'groups.lineItems.contract',
            'groups.lineItems.additionalCosts',
            'account',
            'contact',
        ]);

        $marginLines = [];

        foreach ($quote->groups as $group) {
            foreach ($group->lineItems as $item) {
                $margin = CommercialQuoteLineItemMarginCalculator::forLineItem($item);

                $marginLines[] = [
                    'group_id' => $group->id,
                    'group_title' => $group->title,
                    'line_item_id' => $item->id,
                    'type' => $item->type?->value,
                    'label' => self::lineLabel($item),
                    'margin' => $margin,
                ];
            }
        }

        return [
            'captured_at' => now()->toIso8601String(),
            'preview' => CommercialQuotePreviewBuilder::forQuote($quote),
            'totals' => CommercialQuoteTotalsCalculator::forQuote($quote),
            'margin_lines' => $marginLines,
        ];
    }

    protected static function lineLabel(CommercialQuoteLineItem $item): string
    {
        return match ($item->type) {
            CommercialQuoteLineItemType::Product => (string) ($item->product_alias ?: $item->product?->name ?: 'Producto'),
            CommercialQuoteLineItemType::Personnel => (string) ($item->personnel?->name ?: 'Recurso'),
            CommercialQuoteLineItemType::Contract => (string) ($item->contract?->name ?: 'Contrato'),
            default => 'Partida',
        };
    }
}
