@php
    /** @var array<string, mixed> $preview */
    /** @var callable(float): string $money */
@endphp

<table class="mb-4">
    <thead>
        <tr>
            <th>Customer Information:</th>
            <th>Date:</th>
            <th>Quote #</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                @if (filled($preview['customer_logo_src'] ?? null))
                    <img src="{{ $preview['customer_logo_src'] }}" alt="" class="logo-sm mb-2"/>
                @endif
                <div class="font-semibold">{{ $preview['customer_company'] }}</div>
                <div class="font-semibold mt-4">Contact:</div>
                <div>{{ $preview['contact_name'] }}</div>
                @if (filled($preview['contact_email']))
                    <div>{{ $preview['contact_email'] }}</div>
                @endif
                @if (filled($preview['contact_phone']))
                    <div>{{ $preview['contact_phone'] }}</div>
                @endif
            </td>
            <td>{{ $preview['quoted_at'] }}</td>
            <td class="font-semibold">{{ $preview['quote_number'] }}</td>
        </tr>
        <tr>
            <td class="font-semibold">Terms:</td>
            <td class="font-semibold" colspan="2">Delivery:</td>
        </tr>
        <tr>
            <td>{!! nl2br(e($preview['payment_terms'] ?: '—')) !!}</td>
            <td colspan="2">{{ $preview['delivery_time'] ?: '—' }}</td>
        </tr>
    </tbody>
</table>

@if (filled($preview['project_description']))
    <p class="mb-4 text-muted">{{ $preview['project_description'] }}</p>
@endif

<table>
    <thead>
        <tr>
            <th style="width: 48pt;">QTY</th>
            <th>DESCRIPTION</th>
            <th class="text-end" style="width: 72pt;">UNIT</th>
            <th class="text-end" style="width: 72pt;">SUBTOTAL</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($preview['rows'] as $row)
            @if ($row['type'] === 'group_title')
                <tr>
                    <td></td>
                    <td class="font-semibold" colspan="3">{{ $row['title'] }}</td>
                </tr>
            @elseif ($row['type'] === 'line')
                <tr>
                    <td class="text-end">{{ $row['qty_label'] }}</td>
                    <td>
                        <div class="font-semibold">{{ $row['description_headline'] }}</div>
                        @if (filled($row['description_extra']))
                            <div class="text-muted">{{ $row['description_extra'] }}</div>
                        @endif
                    </td>
                    <td class="text-end">{{ $money($row['unit']) }}</td>
                    <td class="text-end font-semibold">{{ $money($row['subtotal']) }}</td>
                </tr>
            @endif
        @empty
            <tr>
                <td class="text-center text-muted" colspan="4">Sin partidas.</td>
            </tr>
        @endforelse

        @if (count($preview['rows']) > 0)
            @if ($preview['general_discount_percent'] > 0)
                <tr>
                    <td colspan="2"></td>
                    <td class="text-end font-semibold">SUBTOTAL SIN DESCUENTO ({{ $preview['currency_code'] }}):</td>
                    <td class="text-end font-semibold">{{ $money($preview['lines_subtotal']) }}</td>
                </tr>
                <tr>
                    <td colspan="2"></td>
                    <td class="text-end font-semibold">
                        DESCUENTO GENERAL ({{ number_format($preview['general_discount_percent'], 2) }}%):
                    </td>
                    <td class="text-end">−{{ $money($preview['general_discount_amount']) }}</td>
                </tr>
            @endif
            <tr>
                <td colspan="2">
                    @if (filled($preview['tax_note']))
                        <span class="font-semibold">{{ $preview['tax_note'] }}</span>
                    @endif
                </td>
                <td class="text-end font-semibold">SUBTOTAL ({{ $preview['currency_code'] }}):</td>
                <td class="text-end font-semibold">{{ $money($preview['subtotal']) }}</td>
            </tr>
            @if ($preview['vat_percent'] > 0)
                <tr>
                    <td colspan="2"></td>
                    <td class="text-end font-semibold">
                        IVA ({{ number_format($preview['vat_percent'], 2) }}%)
                        @if ($preview['vat_included'])
                            <span class="text-muted">incluido</span>
                        @endif
                    </td>
                    <td class="text-end">{{ $money($preview['vat_amount']) }}</td>
                </tr>
                @if ($preview['vat_included'])
                    <tr>
                        <td colspan="2"></td>
                        <td class="text-end text-muted">Subtotal sin IVA:</td>
                        <td class="text-end text-muted">{{ $money($preview['subtotal_excluding_vat']) }}</td>
                    </tr>
                @endif
            @endif
            <tr>
                <td colspan="2"></td>
                <td class="text-end font-semibold">TOTAL ({{ $preview['currency_code'] }}):</td>
                <td class="text-end font-semibold">{{ $money($preview['total']) }}</td>
            </tr>
        @endif
    </tbody>
</table>

@if (filled($preview['observations']))
    <div class="mt-4">
        <div class="font-semibold">Observations</div>
        <p class="text-muted">{!! nl2br(e($preview['observations'])) !!}</p>
    </div>
@endif

<table class="mt-4" style="max-width: 320pt;">
    <tbody>
        <tr>
            <td class="font-semibold" style="width: 84pt;">TERMS:</td>
            <td>{{ $preview['payment_terms'] ?: '—' }}</td>
        </tr>
        <tr>
            <td class="font-semibold">DELIVERY:</td>
            <td>{{ $preview['delivery_time'] ?: '—' }}</td>
        </tr>
        <tr>
            <td class="font-semibold">F.O.B.:</td>
            <td>{{ $preview['fob'] ?: '—' }}</td>
        </tr>
        <tr>
            <td class="font-semibold">PRICES:</td>
            <td>{{ $preview['prices_note'] }}</td>
        </tr>
    </tbody>
</table>

<div class="confidential">
    <div>{{ $preview['footer_legal'] }}</div>
    <div class="font-semibold mt-4">{{ $preview['footer_confidential'] }}</div>
</div>