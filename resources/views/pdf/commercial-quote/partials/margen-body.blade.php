@php
    /** @var array<string, mixed> $report */
    /** @var callable(float): string $money */
    /** @var callable(float): string $percent */
@endphp

<h1>Reporte de margen — {{ $report['quote_number'] }}</h1>
<p class="text-muted mb-4">
    Fecha: {{ $report['quoted_at'] }} · Moneda de presentación: {{ $report['currency_code'] }} ·
    Tipo de cambio (USD → MXN): {{ $report['exchange_rate'] }}
</p>

@foreach ($report['groups'] as $group)
    <h2>{{ $group['title'] }}@if ($group['exclude_from_margin']) <span class="text-muted">(excluido del margen global)</span>@endif</h2>

    @if ($group['lines'] === [])
        <p class="text-muted mb-4">Sin partidas en este grupo.</p>
        @continue
    @endif

    <table class="mb-4">
        <thead>
            <tr>
                <th>Tipo</th>
                <th>Partida</th>
                <th class="text-end">Importe proveedor</th>
                <th class="text-end">Importe cliente</th>
                <th class="text-end">Margen %</th>
                <th class="text-end">Margen $</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($group['lines'] as $line)
                <tr>
                    <td>{{ $line['type_label'] }}</td>
                    <td>{{ $line['label'] }}</td>
                    <td class="text-end">{{ $money($line['supplier_amount']) }}</td>
                    <td class="text-end">{{ $money($line['customer_amount']) }}</td>
                    <td class="text-end">{{ $percent($line['margin_percent']) }}</td>
                    <td class="text-end">{{ $money($line['margin_amount']) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endforeach

@php $totals = $report['totals']; @endphp
<table style="max-width: 360pt;">
    <tbody>
        <tr class="totals-row">
            <td class="font-semibold">Margen total %</td>
            <td class="text-end">{{ $percent((float) ($totals['margin_percent'] ?? 0)) }}</td>
        </tr>
        <tr class="totals-row">
            <td class="font-semibold">Margen total $</td>
            <td class="text-end">{{ $money((float) ($totals['margin_amount'] ?? 0)) }}</td>
        </tr>
    </tbody>
</table>