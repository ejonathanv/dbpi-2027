<?php

namespace App\Support;

use App\Enums\CommercialQuoteWorkflowStep;
use App\Enums\CrmOpportunityStage;
use App\Models\CommercialQuote;
use App\Models\CrmOpportunity;

final class CrmOpportunityStageSync
{
    public static function syncFromQuote(CommercialQuote $quote): void
    {
        $opportunityId = $quote->crm_opportunity_id;

        if (blank($opportunityId)) {
            return;
        }

        $opportunity = CrmOpportunity::query()->find($opportunityId);

        if (! $opportunity instanceof CrmOpportunity) {
            return;
        }

        $quotes = CommercialQuote::query()
            ->where('crm_opportunity_id', $opportunityId)
            ->get();

        if ($quotes->isEmpty()) {
            $opportunity->update(['stage' => CrmOpportunityStage::Prospeccion]);

            return;
        }

        $stage = $quotes
            ->map(fn (CommercialQuote $item): CrmOpportunityStage => self::mapWorkflowStep($item->activeWorkflowStep()))
            ->sortByDesc(fn (CrmOpportunityStage $item): int => $item->syncPriority())
            ->first();

        if (! $stage instanceof CrmOpportunityStage) {
            return;
        }

        if ($opportunity->stage !== $stage) {
            $opportunity->update(['stage' => $stage]);
        }
    }

    public static function mapWorkflowStep(CommercialQuoteWorkflowStep $workflowStep): CrmOpportunityStage
    {
        return match ($workflowStep) {
            CommercialQuoteWorkflowStep::InProgress => CrmOpportunityStage::CotizacionEnProceso,
            CommercialQuoteWorkflowStep::WaitingInternalAuthorization,
            CommercialQuoteWorkflowStep::InternallyAuthorized => CrmOpportunityStage::AutorizacionInterna,
            CommercialQuoteWorkflowStep::AwaitingClientAuthorization => CrmOpportunityStage::CotizacionEnviada,
            CommercialQuoteWorkflowStep::NegotiationReview => CrmOpportunityStage::NegociacionRevision,
            CommercialQuoteWorkflowStep::ClientAuthorized => CrmOpportunityStage::CerradoGanado,
            CommercialQuoteWorkflowStep::Cancelled => CrmOpportunityStage::CerradoPerdido,
        };
    }
}