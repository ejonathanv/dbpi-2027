<?php

namespace App\Support;

use App\Enums\SalaryCurrency;
use App\Models\CommercialQuote;
use App\Models\CrmOpportunity;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Carbon;

final class CommercialQuoteFromOpportunity
{
    public static function create(CrmOpportunity $opportunity, ?User $creator = null): CommercialQuote
    {
        $opportunity->loadMissing(['account', 'contact']);

        return CommercialQuote::query()->create([
            'quote_number' => CommercialQuote::generateQuoteNumber(),
            'crm_opportunity_id' => $opportunity->getKey(),
            'crm_account_id' => $opportunity->crm_account_id,
            'crm_contact_id' => $opportunity->crm_contact_id,
            'payment_terms' => $opportunity->resolvedPaymentTermsText(),
            'delivery_time' => $opportunity->estimated_delivery_time,
            'project_description' => $opportunity->description,
            'quoted_at' => Carbon::today(),
            'presentation_currency' => SalaryCurrency::Mxn,
            'exchange_rate' => SystemSetting::getDecimal('exchange_rate_usd_mxn', 18),
            'validity_days' => 30,
            'fob' => $opportunity->fob,
            'general_discount_percent' => 0,
            'vat_percent' => $opportunity->resolvedVatPercentForQuote(),
            'vat_included' => true,
            'created_by_user_id' => $creator?->getKey(),
        ]);
    }
}
