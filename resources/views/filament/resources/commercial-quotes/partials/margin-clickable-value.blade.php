@props([
    'action',
    'lineItemId',
    'default' => 0.0,
    'effective' => 0.0,
    'format' => 'money',
])

@php
    $isOverridden = abs((float) $effective - (float) $default) > 0.009;
    $formattedDefault = $format === 'percent'
        ? number_format((float) $default, 2, '.', ',').'%'
        : '$'.number_format((float) $default, 2, '.', ',');
    $formattedEffective = $format === 'percent'
        ? number_format((float) $effective, 2, '.', ',').'%'
        : '$'.number_format((float) $effective, 2, '.', ',');
@endphp

<button
    type="button"
    wire:click="mountAction('{{ $action }}', { lineItemId: {{ $lineItemId }} })"
    class="w-full cursor-pointer rounded px-0.5 py-0.5 text-end transition hover:bg-gray-100 dark:hover:bg-white/10"
    title="Clic para ajustar manualmente"
>
    @if ($isOverridden)
        <div class="text-[8px] text-gray-400 line-through dark:text-gray-500">{{ $formattedDefault }}</div>
        <div @class([
            'font-medium' => $format === 'money',
            'font-semibold text-primary-600 dark:text-primary-400' => $action === 'overrideMarginAmount',
        ])>{{ $formattedEffective }}</div>
    @else
        <div @class([
            'font-medium' => $format === 'money' && $action === 'overrideCustomerAmount',
            'font-semibold text-primary-600 dark:text-primary-400' => $action === 'overrideMarginAmount',
        ])>{{ $formattedEffective }}</div>
    @endif
</button>