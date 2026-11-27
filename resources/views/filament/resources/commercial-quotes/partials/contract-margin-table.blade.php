@php
    use App\Support\CommercialQuoteContractMarginCalculator;

    $margin = CommercialQuoteContractMarginCalculator::forLineItem($item);

    $money = static fn (float $value): string => '$'.number_format($value, 2, '.', ',');
    $percent = static fn (float $value): string => number_format($value, 2, '.', ',').'%';
@endphp

<div class="overflow-x-auto rounded-md border border-gray-200 p-1 dark:border-white/10">
    <table class="w-full min-w-[40rem] table-auto text-start text-[10px] leading-tight">
        <thead>
            <tr class="divide-x divide-gray-200 dark:divide-white/10">
                <th class="bg-[#e8e9f6] px-1 py-0.5 text-center text-[10px] font-semibold text-gray-950 dark:text-gray-900">Contrato</th>
                <th colspan="4" class="bg-[#fffce3] px-1 py-0.5 text-center text-[10px] font-semibold text-gray-950 dark:text-gray-900">Cliente</th>
                <th colspan="2" class="bg-[#f0f8f4] px-1 py-0.5 text-center text-[10px] font-semibold text-gray-950 dark:text-gray-900">Margen</th>
            </tr>
            <tr class="divide-x divide-gray-200 border-t border-gray-200 dark:divide-white/10 dark:border-white/10">
                <th class="bg-[#e8e9f6] px-1 py-0.5 text-start font-medium text-gray-600 dark:text-gray-800">Nombre</th>
                <th class="bg-[#fffce3] px-1 py-0.5 font-medium text-gray-600 dark:text-gray-800">Costo</th>
                <th class="bg-[#fffce3] px-1 py-0.5 font-medium text-gray-600 dark:text-gray-800">% Desc</th>
                <th class="bg-[#fffce3] px-1 py-0.5 font-medium text-gray-600 dark:text-gray-800">$ Desc</th>
                <th class="bg-[#fffce3] px-1 py-0.5 font-medium text-gray-600 dark:text-gray-800">Importe</th>
                <th class="bg-[#f0f8f4] px-1 py-0.5 font-medium text-gray-600 dark:text-gray-800">%</th>
                <th class="bg-[#f0f8f4] px-1 py-0.5 font-medium text-gray-600 dark:text-gray-800">$</th>
            </tr>
        </thead>
        <tbody>
            <tr class="divide-x divide-gray-200 bg-white dark:divide-white/10 dark:bg-gray-900">
                <td class="max-w-[14rem] truncate px-1 py-0.5 text-start font-medium text-gray-900 dark:text-gray-100" title="{{ $margin['contract_name'] }}">
                    {{ $margin['contract_name'] }}
                </td>
                <td class="px-1 py-0.5 align-top">
                    @include('filament.resources.commercial-quotes.partials.margin-clickable-value', [
                        'action' => 'overrideCustomerCost',
                        'lineItemId' => $item->id,
                        'default' => $margin['default_customer_cost'],
                        'effective' => $margin['customer_cost'],
                        'format' => 'money',
                    ])
                </td>
                <td class="px-1 py-0.5 text-end">{{ $percent($margin['customer_discount_percent']) }}</td>
                <td class="px-1 py-0.5 text-end">{{ $money($margin['customer_discount_amount']) }}</td>
                <td class="px-1 py-0.5 align-top">
                    @include('filament.resources.commercial-quotes.partials.margin-clickable-value', [
                        'action' => 'overrideCustomerAmount',
                        'lineItemId' => $item->id,
                        'default' => $margin['default_customer_amount'],
                        'effective' => $margin['customer_amount'],
                        'format' => 'money',
                    ])
                </td>
                <td class="px-1 py-0.5 align-top">
                    @include('filament.resources.commercial-quotes.partials.margin-clickable-value', [
                        'action' => 'overrideMarginPercent',
                        'lineItemId' => $item->id,
                        'default' => $margin['default_margin_percent'],
                        'effective' => $margin['margin_percent'],
                        'format' => 'percent',
                    ])
                </td>
                <td class="px-1 py-0.5 align-top">
                    @include('filament.resources.commercial-quotes.partials.margin-clickable-value', [
                        'action' => 'overrideMarginAmount',
                        'lineItemId' => $item->id,
                        'default' => $margin['default_margin_amount'],
                        'effective' => $margin['margin_amount'],
                        'format' => 'money',
                    ])
                </td>
            </tr>
        </tbody>
    </table>
</div>