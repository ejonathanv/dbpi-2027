<?php

namespace App\Support;

use App\Enums\CommercialQuoteAuthorizationStatus;
use App\Enums\CommercialQuoteWorkflowStep;
use App\Models\CommercialQuote;
use Illuminate\Validation\ValidationException;

class CommercialQuoteWorkflowService
{
    public static function markSentToClientForAuthorization(CommercialQuote $quote): void
    {
        if ($quote->authorization_status !== CommercialQuoteAuthorizationStatus::Authorized) {
            throw ValidationException::withMessages([
                'workflow' => 'La cotización debe estar autorizada internamente.',
            ]);
        }

        $active = $quote->activeWorkflowStep();

        if ($active !== CommercialQuoteWorkflowStep::InternallyAuthorized) {
            throw ValidationException::withMessages([
                'workflow' => 'La cotización no está en etapa de autorización interna.',
            ]);
        }

        $quote->update([
            'workflow_step' => CommercialQuoteWorkflowStep::AwaitingClientAuthorization,
        ]);
    }

    public static function markClientAuthorized(CommercialQuote $quote): void
    {
        if ($quote->authorization_status !== CommercialQuoteAuthorizationStatus::Authorized) {
            throw ValidationException::withMessages([
                'workflow' => 'La cotización debe estar autorizada internamente.',
            ]);
        }

        $active = $quote->activeWorkflowStep();

        if ($active !== CommercialQuoteWorkflowStep::AwaitingClientAuthorization) {
            throw ValidationException::withMessages([
                'workflow' => 'La cotización debe estar en espera de autorización del cliente.',
            ]);
        }

        $quote->update([
            'workflow_step' => CommercialQuoteWorkflowStep::ClientAuthorized,
        ]);
    }

    public static function markRequiresNegotiationOrRevision(CommercialQuote $quote): void
    {
        if ($quote->authorization_status !== CommercialQuoteAuthorizationStatus::Authorized) {
            throw ValidationException::withMessages([
                'workflow' => 'La cotización debe haber sido autorizada internamente.',
            ]);
        }

        if ($quote->activeWorkflowStep() !== CommercialQuoteWorkflowStep::AwaitingClientAuthorization) {
            throw ValidationException::withMessages([
                'workflow' => 'La cotización debe estar en espera de autorización del cliente.',
            ]);
        }

        $quote->update([
            'workflow_step' => CommercialQuoteWorkflowStep::NegotiationReview,
            'authorization_status' => CommercialQuoteAuthorizationStatus::ChangesRequested,
            'frozen_snapshot' => null,
            'authorization_locked_at' => null,
        ]);
    }

    public static function cancelQuote(CommercialQuote $quote): void
    {
        if (! $quote->canCancelQuote()) {
            throw ValidationException::withMessages([
                'workflow' => 'La cotización no puede cancelarse en esta etapa.',
            ]);
        }

        $quote->update([
            'workflow_step' => CommercialQuoteWorkflowStep::Cancelled,
        ]);
    }

    public static function resumeQuote(CommercialQuote $quote): void
    {
        if (! $quote->canResumeQuote()) {
            throw ValidationException::withMessages([
                'workflow' => 'Solo se pueden reanudar cotizaciones canceladas.',
            ]);
        }

        $quote->update([
            'workflow_step' => CommercialQuoteWorkflowStep::InProgress,
            'authorization_status' => CommercialQuoteAuthorizationStatus::Draft,
            'frozen_snapshot' => null,
            'authorization_locked_at' => null,
        ]);
    }
}