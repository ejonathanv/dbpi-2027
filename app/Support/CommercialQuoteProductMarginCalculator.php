<?php

namespace App\Support;

use App\Models\CommercialQuoteLineItem;

class CommercialQuoteProductMarginCalculator
{
    /**
     * @return array{
     *     reseller_price: float,
     *     supplier_discount_percent: float,
     *     supplier_special_discount_percent: float,
     *     supplier_special_discount_amount: float,
     *     net_supplier_unit: float,
     *     additional_per_unit: float,
     *     additional_total: float,
     *     additional_breakdown: array<int, array{concept: string, amount: float}>,
     *     extended_unit_price: float,
     *     supplier_amount_net: float,
     *     supplier_amount: float,
     *     customer_cost: float,
     *     customer_discount_percent: float,
     *     customer_discount_amount: float,
     *     customer_amount: float,
     *     margin_percent: float,
     *     margin_amount: float,
     *     quantity: float,
     * }
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
        $customerAmount = $customerUnitAfterDiscount * $quantity;

        $marginAmount = $customerAmount - $supplierAmount;
        $marginPercent = $customerAmount > 0
            ? ($marginAmount / $customerAmount) * 100
            : 0.0;

        if ($item->product_exclude_from_margin) {
            $marginAmount = 0.0;
            $marginPercent = 0.0;
        }

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
            'customer_amount' => $customerAmount,
            'margin_percent' => $marginPercent,
            'margin_amount' => $marginAmount,
            'quantity' => $quantity,
        ];
    }
}
