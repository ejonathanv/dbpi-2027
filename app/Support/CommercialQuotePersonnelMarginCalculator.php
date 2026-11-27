<?php

namespace App\Support;

use App\Models\CommercialQuoteLineItem;

class CommercialQuotePersonnelMarginCalculator
{
    /**
     * @return array<string, mixed>
     */
    public static function forLineItem(CommercialQuoteLineItem $item): array
    {
        $item->loadMissing('personnel');

        $quantity = max((float) $item->personnel_quantity, 0);
        $days = max((int) $item->personnel_days, 0);
        $amountMultiplier = $quantity * $days;

        $dailyCostMxn = max((float) ($item->personnel?->daily_cost_mxn ?? 0), 0);
        $dailyCostUsd = max((float) ($item->personnel?->daily_cost_usd ?? 0), 0);
        $customerDiscountPercent = max((float) $item->customer_discount_percent, 0);

        $supplierAmount = $dailyCostUsd * $amountMultiplier;

        $defaultCustomerCost = $dailyCostUsd;
        $defaultCustomerDiscountAmount = $defaultCustomerCost * ($customerDiscountPercent / 100);
        $defaultCustomerUnitAfterDiscount = $defaultCustomerCost - $defaultCustomerDiscountAmount;
        $defaultCustomerAmount = $defaultCustomerUnitAfterDiscount * $amountMultiplier;

        $defaultMarginAmount = $defaultCustomerAmount - $supplierAmount;
        $defaultMarginPercent = $defaultCustomerAmount > 0
            ? ($defaultMarginAmount / $defaultCustomerAmount) * 100
            : 0.0;

        $effective = CommercialQuoteMarginOverrideSupport::applyManualOverrides(
            $item,
            $supplierAmount,
            $defaultCustomerCost,
            $defaultCustomerAmount,
            $defaultMarginAmount,
            $defaultMarginPercent,
            $customerDiscountPercent,
            $amountMultiplier,
            false,
        );

        return [
            'personnel_quantity' => $quantity,
            'personnel_days' => $days,
            'daily_cost_mxn' => $dailyCostMxn,
            'daily_cost_usd' => $dailyCostUsd,
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
        ];
    }
}
