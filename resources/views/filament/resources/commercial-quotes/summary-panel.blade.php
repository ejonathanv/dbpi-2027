@php
    $preview = $this->preview;
    $money = static fn (float $value): string => '$' . number_format($value, 2, '.', ',');
@endphp

<div class="fi-resource-relation-manager">
    <div class="mx-auto max-w-4xl rounded-xl border border-gray-200 bg-white p-8 text-[11px] leading-snug text-gray-900 shadow-sm dark:border-white/10 dark:bg-gray-900 dark:text-gray-100">
        <div class="mb-6 text-center text-[10px] font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
            @foreach ($preview['issuer_address_lines'] as $line)
                <div>{{ $line }}</div>
            @endforeach
        </div>

        <table class="mb-4 w-full border-collapse text-[10px]">
            <thead>
                <tr>
                    <th class="border border-gray-300 px-2 py-1 text-start font-semibold dark:border-white/20">Customer Information:</th>
                    <th class="border border-gray-300 px-2 py-1 text-start font-semibold dark:border-white/20">Date:</th>
                    <th class="border border-gray-300 px-2 py-1 text-start font-semibold dark:border-white/20">Quote #</th>
                </tr>
            </thead>
            <tbody>
                <tr class="align-top">
                    <td class="border border-gray-300 px-2 py-2 dark:border-white/20">
                        @if (filled($preview['customer_logo_url'] ?? null))
                            <img
                                src="{{ $preview['customer_logo_url'] }}"
                                alt=""
                                class="mb-1.5 max-h-8 w-auto max-w-[7rem] object-contain object-left"
                            />
                        @endif
                        <div class="font-semibold">{{ $preview['customer_company'] }}</div>
                        <div class="mt-2 text-[10px] font-semibold">Contact:</div>
                        <div>{{ $preview['contact_name'] }}</div>
                        @if (filled($preview['contact_email']))
                            <div>{{ $preview['contact_email'] }}</div>
                        @endif
                        @if (filled($preview['contact_phone']))
                            <div>{{ $preview['contact_phone'] }}</div>
                        @endif
                    </td>
                    <td class="border border-gray-300 px-2 py-2 dark:border-white/20">{{ $preview['quoted_at'] }}</td>
                    <td class="border border-gray-300 px-2 py-2 font-medium dark:border-white/20">{{ $preview['quote_number'] }}</td>
                </tr>
                <tr>
                    <td class="border border-gray-300 px-2 py-1 font-semibold dark:border-white/20">Terms:</td>
                    <td class="border border-gray-300 px-2 py-1 font-semibold dark:border-white/20" colspan="2">Delivery:</td>
                </tr>
                <tr class="align-top">
                    <td class="border border-gray-300 px-2 py-2 dark:border-white/20 whitespace-pre-line">{{ $preview['payment_terms'] ?: '—' }}</td>
                    <td class="border border-gray-300 px-2 py-2 dark:border-white/20" colspan="2">{{ $preview['delivery_time'] ?: '—' }}</td>
                </tr>
            </tbody>
        </table>

        @if (filled($preview['project_description']))
            <p class="mb-4 text-[10px] text-gray-700 dark:text-gray-300">{{ $preview['project_description'] }}</p>
        @endif

        <table class="w-full border-collapse text-[10px]">
            <thead>
                <tr class="bg-gray-100 dark:bg-white/5">
                    <th class="w-14 border border-gray-300 px-2 py-1 text-start font-semibold dark:border-white/20">QTY</th>
                    <th class="border border-gray-300 px-2 py-1 text-start font-semibold dark:border-white/20">DESCRIPTION</th>
                    <th class="w-24 border border-gray-300 px-2 py-1 text-end font-semibold dark:border-white/20">UNIT</th>
                    <th class="w-24 border border-gray-300 px-2 py-1 text-end font-semibold dark:border-white/20">SUBTOTAL</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($preview['rows'] as $row)
                    @if ($row['type'] === 'group_title')
                        <tr>
                            <td class="border border-gray-300 px-2 py-2 dark:border-white/20"></td>
                            <td class="border border-gray-300 px-2 py-2 font-semibold dark:border-white/20" colspan="3">{{ $row['title'] }}</td>
                        </tr>
                    @elseif ($row['type'] === 'line')
                        <tr class="align-top">
                            <td class="border border-gray-300 px-2 py-2 text-end dark:border-white/20">{{ $row['qty_label'] }}</td>
                            <td class="border border-gray-300 px-2 py-2 dark:border-white/20">
                                <div class="font-medium">{{ $row['description_headline'] }}</div>
                                @if (filled($row['description_extra']))
                                    <div class="mt-1 text-[9px] text-gray-600 dark:text-gray-400">{{ $row['description_extra'] }}</div>
                                @endif
                            </td>
                            <td class="border border-gray-300 px-2 py-2 text-end dark:border-white/20">{{ $money($row['unit']) }}</td>
                            <td class="border border-gray-300 px-2 py-2 text-end font-medium dark:border-white/20">{{ $money($row['subtotal']) }}</td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td class="border border-gray-300 px-2 py-4 text-center text-gray-500 dark:border-white/20" colspan="4">
                            Sin partidas. Configure grupos y elementos en la pestaña Margen.
                        </td>
                    </tr>
                @endforelse

                @if (count($preview['rows']) > 0)
                    @if ($preview['general_discount_percent'] > 0)
                        <tr>
                            <td class="border border-gray-300 dark:border-white/20" colspan="2"></td>
                            <td class="border border-gray-300 px-2 py-1 text-end font-semibold dark:border-white/20">SUBTOTAL SIN DESCUENTO ({{ $preview['currency_code'] }}):</td>
                            <td class="border border-gray-300 px-2 py-2 text-end font-semibold dark:border-white/20">{{ $money($preview['lines_subtotal']) }}</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-300 dark:border-white/20" colspan="2"></td>
                            <td class="border border-gray-300 px-2 py-1 text-end font-semibold dark:border-white/20">
                                DESCUENTO GENERAL ({{ number_format($preview['general_discount_percent'], 2) }}%):
                            </td>
                            <td class="border border-gray-300 px-2 py-2 text-end text-red-700 dark:text-red-400">−{{ $money($preview['general_discount_amount']) }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td class="border border-gray-300 px-2 py-2 dark:border-white/20" colspan="2">
                            @if (filled($preview['tax_note']))
                                <span class="font-semibold">{{ $preview['tax_note'] }}</span>
                            @endif
                        </td>
                        <td class="border border-gray-300 px-2 py-1 text-end font-semibold dark:border-white/20">SUBTOTAL ({{ $preview['currency_code'] }}):</td>
                        <td class="border border-gray-300 px-2 py-2 text-end font-semibold dark:border-white/20">{{ $money($preview['subtotal']) }}</td>
                    </tr>
                    @if ($preview['vat_percent'] > 0)
                        <tr>
                            <td class="border border-gray-300 dark:border-white/20" colspan="2"></td>
                            <td class="border border-gray-300 px-2 py-1 text-end font-semibold dark:border-white/20">
                                IVA ({{ number_format($preview['vat_percent'], 2) }}%)
                                @if ($preview['vat_included'])
                                    <span class="block text-[8px] font-normal text-gray-500">incluido</span>
                                @endif
                            </td>
                            <td class="border border-gray-300 px-2 py-2 text-end dark:border-white/20">{{ $money($preview['vat_amount']) }}</td>
                        </tr>
                        @if ($preview['vat_included'])
                            <tr>
                                <td class="border border-gray-300 dark:border-white/20" colspan="2"></td>
                                <td class="border border-gray-300 px-2 py-1 text-end text-[9px] text-gray-600 dark:border-white/20 dark:text-gray-400">Subtotal sin IVA:</td>
                                <td class="border border-gray-300 px-2 py-2 text-end text-[9px] text-gray-600 dark:border-white/20 dark:text-gray-400">{{ $money($preview['subtotal_excluding_vat']) }}</td>
                            </tr>
                        @endif
                    @endif
                    <tr>
                        <td class="border border-gray-300 dark:border-white/20" colspan="2"></td>
                        <td class="border border-gray-300 px-2 py-1 text-end font-semibold dark:border-white/20">TOTAL ({{ $preview['currency_code'] }}):</td>
                        <td class="border border-gray-300 px-2 py-2 text-end text-sm font-bold dark:border-white/20">{{ $money($preview['total']) }}</td>
                    </tr>
                @endif
            </tbody>
        </table>

        @if (filled($preview['observations']))
            <div class="mt-6">
                <div class="font-semibold">Observations</div>
                <p class="mt-1 whitespace-pre-line text-[10px]">{{ $preview['observations'] }}</p>
            </div>
        @endif

        <table class="mt-6 w-full max-w-lg border-collapse text-[10px]">
            <tbody>
                <tr>
                    <td class="border border-gray-300 px-2 py-1 w-28 font-semibold dark:border-white/20">TERMS:</td>
                    <td class="border border-gray-300 px-2 py-1 dark:border-white/20">{{ $preview['payment_terms'] ?: '—' }}</td>
                </tr>
                <tr>
                    <td class="border border-gray-300 px-2 py-1 font-semibold dark:border-white/20">DELIVERY:</td>
                    <td class="border border-gray-300 px-2 py-1 dark:border-white/20">{{ $preview['delivery_time'] ?: '—' }}</td>
                </tr>
                <tr>
                    <td class="border border-gray-300 px-2 py-1 font-semibold dark:border-white/20">F.O.B.:</td>
                    <td class="border border-gray-300 px-2 py-1 dark:border-white/20">{{ $preview['fob'] ?: '—' }}</td>
                </tr>
                <tr>
                    <td class="border border-gray-300 px-2 py-1 font-semibold dark:border-white/20">PRICES:</td>
                    <td class="border border-gray-300 px-2 py-1 dark:border-white/20">{{ $preview['prices_note'] }}</td>
                </tr>
            </tbody>
        </table>

        <div class="mt-10 text-center text-[9px] text-gray-600 dark:text-gray-400">
            <div>{{ $preview['footer_legal'] }}</div>
            <div class="mt-2 font-semibold uppercase">{{ $preview['footer_confidential'] }}</div>
        </div>
    </div>
</div>