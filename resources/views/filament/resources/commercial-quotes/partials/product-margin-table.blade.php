@php
    use App\Support\CommercialQuoteProductMarginCalculator;

    $margin = CommercialQuoteProductMarginCalculator::forLineItem($item);

    $money = static fn (float $value): string => '$'.number_format($value, 2, '.', ',');
    $percent = static fn (float $value): string => number_format($value, 2, '.', ',').'%';
@endphp

<div class="overflow-x-auto rounded-md border border-gray-200 p-1 dark:border-white/10">
    <table class="w-full min-w-[58rem] table-auto text-start text-[10px] leading-tight">
        <thead>
            <tr class="divide-x divide-gray-200 dark:divide-white/10">
                <th colspan="6" class="bg-[#e8e9f6] px-1 py-0.5 text-center text-[10px] font-semibold text-gray-950 dark:text-gray-900">Proveedor</th>
                <th colspan="4" class="bg-[#fffce3] px-1 py-0.5 text-center text-[10px] font-semibold text-gray-950 dark:text-gray-900">Cliente</th>
                <th colspan="2" class="bg-[#f0f8f4] px-1 py-0.5 text-center text-[10px] font-semibold text-gray-950 dark:text-gray-900">Margen</th>
            </tr>
            <tr class="divide-x divide-gray-200 border-t border-gray-200 dark:divide-white/10 dark:border-white/10">
                <th class="bg-[#e8e9f6] px-1 py-0.5 font-medium text-gray-600 dark:text-gray-800">Reseller price</th>
                <th class="bg-[#e8e9f6] px-1 py-0.5 font-medium text-gray-600 dark:text-gray-800">% Desc</th>
                <th class="bg-[#e8e9f6] px-1 py-0.5 font-medium text-gray-600 dark:text-gray-800">% Desc especial</th>
                <th class="bg-[#e8e9f6] px-1 py-0.5 font-medium text-gray-600 dark:text-gray-800">$ Desc especial</th>
                <th class="bg-[#e8e9f6] px-1 py-0.5 font-medium text-gray-600 dark:text-gray-800">Precio extendido</th>
                <th class="bg-[#e8e9f6] px-1 py-0.5 font-medium text-gray-600 dark:text-gray-800">Importe</th>
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
                <td class="px-1 py-0.5 text-end text-gray-800 dark:text-gray-100">{{ $money($margin['reseller_price']) }}</td>
                <td class="px-1 py-0.5 text-end">{{ $percent($margin['supplier_discount_percent']) }}</td>
                <td class="px-1 py-0.5 text-end">{{ $percent($margin['supplier_special_discount_percent']) }}</td>
                <td class="px-1 py-0.5 text-end">{{ $money($margin['supplier_special_discount_amount']) }}</td>
                <td class="px-1 py-0.5 text-end align-top">
                    <div>{{ $money($margin['net_supplier_unit']) }}</div>
                    @if ($margin['additional_breakdown'] !== [])
                        <div class="mt-0.5 space-y-px text-end text-[8px] leading-snug text-gray-500 dark:text-gray-400">
                            @foreach ($margin['additional_breakdown'] as $additionalCost)
                                <div>{{ $additionalCost['concept'] }}: {{ $money($additionalCost['amount']) }}</div>
                            @endforeach
                        </div>
                    @endif
                </td>
                <td class="px-1 py-0.5 text-end align-top font-medium">
                    <div>{{ $money($margin['supplier_amount_net']) }}</div>
                    @if ($margin['additional_total'] > 0)
                        <div class="mt-0.5 text-[8px] font-normal text-gray-500 dark:text-gray-400">
                            Adicionales: {{ $money($margin['additional_total']) }}
                        </div>
                    @endif
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