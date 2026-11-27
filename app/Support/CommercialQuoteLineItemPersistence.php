<?php

namespace App\Support;

use App\Enums\CommercialQuoteLineItemType;
use App\Models\CommercialQuoteGroup;
use App\Models\CommercialQuoteLineItem;

class CommercialQuoteLineItemPersistence
{
    /**
     * @param  array<string, mixed>  $data
     */
    public static function createForGroup(CommercialQuoteGroup $group, CommercialQuoteLineItemType $type, array $data): CommercialQuoteLineItem
    {
        $additionalCosts = $data['additionalCosts'] ?? [];
        unset($data['additionalCosts']);

        $data['type'] = $type;
        $sortOrder = (int) $group->lineItems()->max('sort_order') + 1;
        $data['sort_order'] = $sortOrder;

        /** @var CommercialQuoteLineItem $item */
        $item = $group->lineItems()->create($data);

        if ($type === CommercialQuoteLineItemType::Product) {
            self::syncAdditionalCosts($item, is_array($additionalCosts) ? $additionalCosts : []);
        }

        return $item;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function updateLineItem(CommercialQuoteLineItem $item, array $data): CommercialQuoteLineItem
    {
        $additionalCosts = $data['additionalCosts'] ?? null;
        unset($data['additionalCosts'], $data['type']);

        $item->update($data);

        if ($item->type === CommercialQuoteLineItemType::Product && is_array($additionalCosts)) {
            self::syncAdditionalCosts($item, $additionalCosts);
        }

        return $item->refresh();
    }

    /**
     * @param  array<int, array<string, mixed>>  $costs
     */
    public static function syncAdditionalCosts(CommercialQuoteLineItem $item, array $costs): void
    {
        $item->additionalCosts()->delete();

        foreach (array_values($costs) as $sort => $cost) {
            if (blank($cost['concept'] ?? null)) {
                continue;
            }

            $item->additionalCosts()->create([
                'concept' => $cost['concept'],
                'amount' => $cost['amount'] ?? 0,
                'sort_order' => $sort,
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public static function productFormData(CommercialQuoteLineItem $item): array
    {
        return array_merge($item->attributesToArray(), [
            'additionalCosts' => $item->additionalCosts()
                ->orderBy('sort_order')
                ->get()
                ->map(fn ($cost): array => [
                    'concept' => $cost->concept,
                    'amount' => $cost->amount,
                ])
                ->all(),
        ]);
    }
}
