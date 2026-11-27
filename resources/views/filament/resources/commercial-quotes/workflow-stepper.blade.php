@php
    use App\Enums\CommercialQuoteWorkflowStep;

    /** @var \App\Models\CommercialQuote|null $quote */
    $quote = $quote ?? null;

    if (! $quote) {
        return;
    }

    $steps = CommercialQuoteWorkflowStep::pipeline();
@endphp

<div class="fi-commercial-quote-workflow mb-2 w-full">
    <div class="overflow-x-auto pb-0.5">
        <div class="fi-cq-workflow-track relative min-w-[48rem] w-full">
            <span class="fi-cq-workflow-line" aria-hidden="true"></span>

            <ol class="relative z-10 flex w-full items-center justify-between gap-1">
                @foreach ($steps as $step)
                    @php
                        $state = $quote->workflowStepVisualState($step);
                    @endphp
                    <li wire:key="workflow-step-{{ $step->value }}" class="shrink-0">
                        <span
                            @class([
                                'fi-cq-workflow-pill inline-flex items-center justify-center rounded-full px-2 py-1.5 text-center text-[0.65rem] font-medium leading-tight whitespace-nowrap sm:px-2.5 sm:text-[0.7rem]',
                                'fi-cq-workflow-pill--current' => $state === 'current'
                                    && ! in_array($step, [
                                        CommercialQuoteWorkflowStep::ClientAuthorized,
                                        CommercialQuoteWorkflowStep::Cancelled,
                                    ], true),
                                'fi-cq-workflow-pill--current-success' => $state === 'current'
                                    && $step === CommercialQuoteWorkflowStep::ClientAuthorized,
                                'fi-cq-workflow-pill--current-danger' => $state === 'current'
                                    && $step === CommercialQuoteWorkflowStep::Cancelled,
                                'fi-cq-workflow-pill--muted' => $state !== 'current',
                            ])
                        >
                            {{ $step->getLabel() }}
                        </span>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
</div>