<?php

namespace App\Support;

use App\Enums\CommercialQuoteMarginOverrideType;
use App\Models\CommercialQuoteLineItem;

class CommercialQuoteMarginOverrideSupport
{
    /**
     * @return array{
     *     customer_cost: float,
     *     customer_discount_amount: float,
     *     customer_amount: float,
     *     margin_percent: float,
     *     margin_amount: float,
     * }
     */
    public static function applyManualOverrides(
        CommercialQuoteLineItem $item,
        float $supplierAmount,
        float $defaultCustomerCost,
        float $defaultCustomerAmount,
        float $defaultMarginAmount,
        float $defaultMarginPercent,
        float $customerDiscountPercent,
        float $amountMultiplier,
        bool $excludeFromMargin,
    ): array {
        $customerCost = $defaultCustomerCost;
        $customerDiscountAmount = $customerCost * ($customerDiscountPercent / 100);
        $customerAmount = $defaultCustomerAmount;
        $marginAmount = $defaultMarginAmount;
        $marginPercent = $defaultMarginPercent;

        $type = $item->manual_margin_override_type;
        $value = (float) ($item->manual_margin_override_value ?? 0);

        if ($type === CommercialQuoteMarginOverrideType::CustomerCost && ! $excludeFromMargin) {
            $customerCost = max($value, 0);
            $customerDiscountAmount = $customerCost * ($customerDiscountPercent / 100);
            $customerAmount = ($customerCost - $customerDiscountAmount) * $amountMultiplier;
            $marginAmount = $customerAmount - $supplierAmount;
            $marginPercent = $customerAmount > 0
                ? ($marginAmount / $customerAmount) * 100
                : 0.0;
        } elseif ($type === CommercialQuoteMarginOverrideType::CustomerAmount && ! $excludeFromMargin) {
            $customerAmount = max($value, 0);
            $marginAmount = $customerAmount - $supplierAmount;
            $marginPercent = $customerAmount > 0
                ? ($marginAmount / $customerAmount) * 100
                : 0.0;
            ['cost' => $customerCost, 'discount' => $customerDiscountAmount] = self::deriveUnitCostFromTotal(
                $customerAmount,
                $amountMultiplier,
                $customerDiscountPercent,
            );
        } elseif ($type === CommercialQuoteMarginOverrideType::MarginPercent && ! $excludeFromMargin) {
            $marginPercent = max(min($value, 99.99), 0);
            $customerAmount = $supplierAmount / (1 - ($marginPercent / 100));
            $marginAmount = $customerAmount - $supplierAmount;
            ['cost' => $customerCost, 'discount' => $customerDiscountAmount] = self::deriveUnitCostFromTotal(
                $customerAmount,
                $amountMultiplier,
                $customerDiscountPercent,
            );
        } elseif ($type === CommercialQuoteMarginOverrideType::MarginAmount && ! $excludeFromMargin) {
            $marginAmount = $value;
            $customerAmount = $supplierAmount + $marginAmount;
            $marginPercent = $customerAmount > 0
                ? ($marginAmount / $customerAmount) * 100
                : 0.0;
            ['cost' => $customerCost, 'discount' => $customerDiscountAmount] = self::deriveUnitCostFromTotal(
                $customerAmount,
                $amountMultiplier,
                $customerDiscountPercent,
            );
        }

        return [
            'customer_cost' => $customerCost,
            'customer_discount_amount' => $customerDiscountAmount,
            'customer_amount' => $customerAmount,
            'margin_percent' => $marginPercent,
            'margin_amount' => $marginAmount,
        ];
    }

    /**
     * @return array{cost: float, discount: float}
     */
    public static function deriveUnitCostFromTotal(
        float $customerAmount,
        float $amountMultiplier,
        float $customerDiscountPercent,
    ): array {
        $multiplier = max($amountMultiplier, 0.0001);
        $unitAfterDiscount = $customerAmount / $multiplier;

        if ($customerDiscountPercent <= 0 || $customerDiscountPercent >= 100) {
            return [
                'cost' => $unitAfterDiscount,
                'discount' => 0.0,
            ];
        }

        $unitCost = $unitAfterDiscount / (1 - ($customerDiscountPercent / 100));

        return [
            'cost' => $unitCost,
            'discount' => $unitCost * ($customerDiscountPercent / 100),
        ];
    }
}
