@php
    $preview = $this->preview;
    $money = static fn (float $value): string => '$' . number_format($value, 2, '.', ',');
@endphp

<div class="fi-resource-relation-manager">
    @include('filament.resources.commercial-quotes.partials.quote-summary-document', [
        'preview' => $preview,
        'money' => $money,
    ])
</div>