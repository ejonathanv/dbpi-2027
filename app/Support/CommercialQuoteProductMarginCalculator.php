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

        $customerCost = $extendedUnitPrice;
        $customerDiscountAmount = $customerCost * ($customerDiscountPercent / 100);
        $customerUnitAfterDiscount = $customerCost - $customerDiscountAmount;
        $defaultCustomerAmount = $customerUnitAfterDiscount * $quantity;

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
            $defaultCustomerAmount,
            $defaultMarginAmount,
            $defaultMarginPercent,
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
            'customer_cost' => $customerCost,
            'customer_discount_percent' => $customerDiscountPercent,
            'customer_discount_amount' => $customerDiscountAmount,
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
     * @return array{customer_amount: float, margin_percent: float, margin_amount: float}
     */
    protected static function applyManualOverrides(
        CommercialQuoteLineItem $item,
        float $supplierAmount,
        float $defaultCustomerAmount,
        float $defaultMarginAmount,
        float $defaultMarginPercent,
        bool $excludeFromMargin,
    ): array {
        $customerAmount = $defaultCustomerAmount;
        $marginAmount = $defaultMarginAmount;
        $marginPercent = $defaultMarginPercent;

        $type = $item->manual_margin_override_type;
        $value = (float) ($item->manual_margin_override_value ?? 0);

        if ($type === CommercialQuoteMarginOverrideType::CustomerAmount && ! $excludeFromMargin) {
            $customerAmount = max($value, 0);
            $marginAmount = $customerAmount - $supplierAmount;
            $marginPercent = $customerAmount > 0
                ? ($marginAmount / $customerAmount) * 100
                : 0.0;
        } elseif ($type === CommercialQuoteMarginOverrideType::MarginPercent && ! $excludeFromMargin) {
            $marginPercent = max(min($value, 99.99), 0);
            $customerAmount = $supplierAmount / (1 - ($marginPercent / 100));
            $marginAmount = $customerAmount - $supplierAmount;
        } elseif ($type === CommercialQuoteMarginOverrideType::MarginAmount && ! $excludeFromMargin) {
            $marginAmount = $value;
            $customerAmount = $supplierAmount + $marginAmount;
            $marginPercent = $customerAmount > 0
                ? ($marginAmount / $customerAmount) * 100
                : 0.0;
        }

        return [
            'customer_amount' => $customerAmount,
            'margin_percent' => $marginPercent,
            'margin_amount' => $marginAmount,
        ];
    }
}
