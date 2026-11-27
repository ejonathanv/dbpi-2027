<?php

namespace App\Support;

use App\Models\CommercialQuoteLineItem;

class CommercialQuoteContractMarginCalculator
{
    /**
     * @return array<string, mixed>
     */
    public static function forLineItem(CommercialQuoteLineItem $item): array
    {
        $item->loadMissing('contract');

        $amountMultiplier = 1.0;
        $customerDiscountPercent = max((float) $item->customer_discount_percent, 0);
        $internalCost = max((float) $item->supplier_cost, 0);

        $supplierAmount = $internalCost;

        $defaultCustomerCost = $internalCost;
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
            'contract_name' => (string) ($item->contract?->name ?? '—'),
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
