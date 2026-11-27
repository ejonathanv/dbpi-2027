<?php

namespace App\Observers;

use App\Models\CommercialQuote;
use App\Support\CrmOpportunityStageSync;

class CommercialQuoteObserver
{
    public function saved(CommercialQuote $quote): void
    {
        if (
            $quote->wasRecentlyCreated
            || $quote->wasChanged(['workflow_step', 'authorization_status', 'crm_opportunity_id'])
        ) {
            CrmOpportunityStageSync::syncFromQuote($quote);
        }
    }

    public function deleted(CommercialQuote $quote): void
    {
        CrmOpportunityStageSync::syncFromQuote($quote);
    }

    public function restored(CommercialQuote $quote): void
    {
        CrmOpportunityStageSync::syncFromQuote($quote);
    }
}