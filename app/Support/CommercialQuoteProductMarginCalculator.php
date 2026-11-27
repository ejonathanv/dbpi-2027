<?php

namespace App\Support;

use App\Enums\CommercialQuoteMarginOverrideType;
use App\Models\CommercialQuoteLineItem;

class CommercialQuoteProductMarginCalculator
{
    /**
     * @return array<string, mixed>
     */
    public static function forLineItem(CommercialQuoteLineItem $item): array
    {
        $quantity = max((float) $item->product_quantity, 0);

        $resellerPrice = max((float) $item->supplier_cost, 0);
        $supplierDiscountPercent = max((float) $item->supplier_discount_percent, 0);
        $supplierSpecialDiscountPercent = max((float) $item->supplier_special_discount_percent, 0);
        $customerDiscountPercent = max((float) $item->customer_discount_percent, 0);

        $supplierDiscountAmount = $resellerPrice * ($supplierDiscountPercent / 100);
        $afterSupplierDiscount = $resellerPrice - $supplierDiscountAmount;

        $supplierSpecialDiscountAmount = $afterSupplierDiscount * ($supplierSpecialDiscountPercent / 100);
        $netSupplierUnit = $afterSupplierDiscount - $supplierSpecialDiscountAmount;

        $additionalBreakdown = $item->additionalCosts
            ->map(fn ($cost): array => [
                'concept' => (string) $cost->concept,
                'amount' => (float) $cost->amount,
            ])
            ->values()
            ->all();

        $additionalPerUnit = (float) collect($additionalBreakdown)->sum('amount');
        $additionalTotal = $additionalPerUnit * $quantity;

        $extendedUnitPrice = $netSupplierUnit + $additionalPerUnit;
        $supplierAmountNet = $netSupplierUnit * $quantity;
        $supplierAmount = $extendedUnitPrice * $quantity;

        $defaultCustomerCost = $extendedUnitPrice;
        $defaultCustomerDiscountAmount = $defaultCustomerCost * ($customerDiscountPercent / 100);
        $defaultCustomerUnitAfterDiscount = $defaultCustomerCost - $defaultCustomerDiscountAmount;
        $defaultCustomerAmount = $defaultCustomerUnitAfterDiscount * $quantity;

        $defaultMarginAmount = $defaultCustomerAmount - $supplierAmount;
        $defaultMarginPercent = $defaultCustomerAmount > 0
            ? ($defaultMarginAmount / $defaultCustomerAmount) * 100
            : 0.0;

        if ($item->product_exclude_from_margin) {
            $defaultMarginAmount = 0.0;
            $defaultMarginPercent = 0.0;
        }

        $effective = self::applyManualOverrides(
            $item,
            $supplierAmount,
            $defaultCustomerCost,
            $defaultCustomerAmount,
            $defaultMarginAmount,
            $defaultMarginPercent,
            $customerDiscountPercent,
            $quantity,
            (bool) $item->product_exclude_from_margin,
        );

        return [
            'reseller_price' => $resellerPrice,
            'supplier_discount_percent' => $supplierDiscountPercent,
            'supplier_special_discount_percent' => $supplierSpecialDiscountPercent,
            'supplier_special_discount_amount' => $supplierSpecialDiscountAmount,
            'net_supplier_unit' => $netSupplierUnit,
            'additional_per_unit' => $additionalPerUnit,
            'additional_total' => $additionalTotal,
            'additional_breakdown' => $additionalBreakdown,
            'extended_unit_price' => $extendedUnitPrice,
            'supplier_amount_net' => $supplierAmountNet,
            'supplier_amount' => $supplierAmount,
            'default_customer_cost' => $defaultCustomerCost,
            'customer_cost' => $effective['customer_cost'],
            'customer_discount_percent' => $customerDiscountPercent,
            'customer_discount_amount' => $effective['customer_discount_amount'],
            'default_customer_amount' => $defaultCustomerAmount,
            'default_margin_percent' => $defaultMarginPercent,
            'default_margin_amount' => $defaultMarginAmount,
            'customer_amount' => $effective['customer_amount'],
            'margin_percent' => $effective['margin_percent'],
            'margin_amount' => $effective['margin_amount'],
            'manual_override_type' => $item->manual_margin_override_type?->value,
            'quantity' => $quantity,
        ];
    }

    /**
     * @return array{
     *     customer_cost: float,
     *     customer_discount_amount: float,
     *     customer_amount: float,
     *     margin_percent: float,
     *     margin_amount: float,
     * }
     */
    protected static function applyManualOverrides(
        CommercialQuoteLineItem $item,
        float $supplierAmount,
        float $defaultCustomerCost,
        float $defaultCustomerAmount,
        float $defaultMarginAmount,
        float $defaultMarginPercent,
        float $customerDiscountPercent,
        float $quantity,
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
            $customerAmount = ($customerCost - $customerDiscountAmount) * $quantity;
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
                $quantity,
                $customerDiscountPercent,
            );
        } elseif ($type === CommercialQuoteMarginOverrideType::MarginPercent && ! $excludeFromMargin) {
            $marginPercent = max(min($value, 99.99), 0);
            $customerAmount = $supplierAmount / (1 - ($marginPercent / 100));
            $marginAmount = $customerAmount - $supplierAmount;
            ['cost' => $customerCost, 'discount' => $customerDiscountAmount] = self::deriveUnitCostFromTotal(
                $customerAmount,
                $quantity,
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
                $quantity,
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
    protected static function deriveUnitCostFromTotal(
        float $customerAmount,
        float $quantity,
        float $customerDiscountPercent,
    ): array {
        $qty = max($quantity, 0.0001);
        $unitAfterDiscount = $customerAmount / $qty;

        if ($customerDiscountPercent <= 0) {
            return [
                'cost' => $unitAfterDiscount,
                'discount' => 0.0,
            ];
        }

        if ($customerDiscountPercent >= 100) {
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
