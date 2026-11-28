@php
    use App\Support\CommercialFupCoverBuilder;

    /** @var \App\Models\CommercialFup|null $fup */
    /** @var array<string, mixed>|null $cover */
    $cover = $cover ?? ($fup ? CommercialFupCoverBuilder::forFup($fup) : []);
    $shipping = $cover['shipping'] ?? [];
    $billing = $cover['billing'] ?? [];
    $order = $cover['order_info'] ?? [];
    $final = $cover['final_client'] ?? [];
    $paymentRows = $cover['payment_rows'] ?? [];

    $cell = 'border border-gray-300 px-2 py-1 dark:border-white/20';
    $head = $cell.' font-semibold uppercase';
@endphp

<div class="mx-auto max-w-5xl rounded-xl border border-gray-200 bg-white p-6 text-[10px] leading-snug text-gray-900 shadow-sm dark:border-white/10 dark:bg-gray-900 dark:text-gray-100">
    <div class="mb-4 flex items-start justify-between gap-4">
        <img
            src="{{ $cover['issuer_logo_url'] ?? '' }}"
            alt="Dynamic Communications"
            class="h-10 w-auto max-w-[12rem] object-contain object-left"
        />
        <div class="text-end">
            <div class="text-sm font-bold uppercase tracking-wide">Forma única de pedido</div>
            <div class="mt-1 text-base font-semibold">#{{ $cover['fup_number'] ?? '—' }}</div>
        </div>
    </div>

    <table class="mb-3 w-full border-collapse">
        <tr>
            <td class="{{ $head }}">NO. DE COTIZACIÓN:</td>
            <td class="{{ $head }}">NO. DE ORDEN DEL CLIENTE:</td>
            <td class="{{ $head }}">FECHA:</td>
        </tr>
        <tr>
            <td class="{{ $cell }} py-2">{{ $cover['quote_number'] ?? '—' }}</td>
            <td class="{{ $cell }} py-2">{{ $cover['client_po_number'] ?? '—' }}</td>
            <td class="{{ $cell }} py-2">{{ $cover['fup_date'] ?? '—' }}</td>
        </tr>
    </table>

    <p class="{{ $head }} border border-gray-300 border-b-0 px-2 py-1 dark:border-white/20">NOMBRE COMERCIAL:</p>
    <p class="{{ $cell }} mb-3 border-b py-2">{{ $cover['trade_name'] ?? '—' }}</p>

    <p class="{{ $head }} border border-gray-300 border-b-0 px-2 py-1 dark:border-white/20">DESCRIPCIÓN DEL PROYECTO:</p>
    <p class="{{ $cell }} mb-3 whitespace-pre-line border-b py-2">{{ $cover['project_description'] ?? '—' }}</p>

    <table class="mb-3 w-full border-collapse">
        <tr>
            <td class="{{ $head }}">CONTACTO RESPONSABLE TÉCNICO:</td>
            <td class="{{ $head }}">E-MAIL:</td>
            <td class="{{ $head }}">TELÉFONO:</td>
        </tr>
        <tr>
            <td class="{{ $cell }} py-2">{{ $cover['technical_contact_name'] ?? '—' }}</td>
            <td class="{{ $cell }} py-2">{{ $cover['technical_contact_email'] ?? '—' }}</td>
            <td class="{{ $cell }} py-2">{{ $cover['technical_contact_phone'] ?? '—' }}</td>
        </tr>
    </table>

    <p class="mb-1 font-bold uppercase">Embarque</p>
    <table class="mb-3 w-full border-collapse">
        <tr><td class="{{ $head }}" colspan="2">RAZON SOCIAL:</td></tr>
        <tr><td class="{{ $cell }} py-2" colspan="2">{{ $shipping['legal_name'] ?? '—' }}</td></tr>
        <tr><td class="{{ $head }}" colspan="2">DOMICILIO (CALLE Y NUMERO, COLONIA / PARQUE INDUSTRIAL):</td></tr>
        <tr><td class="{{ $cell }} py-2" colspan="2">{{ $shipping['street'] ?? '—' }}</td></tr>
        <tr>
            <td class="{{ $head }}">CIUDAD Y ESTADO:</td>
            <td class="{{ $head }}">C.P:</td>
        </tr>
        <tr>
            <td class="{{ $cell }} py-2">{{ $shipping['city_state'] ?? '—' }}</td>
            <td class="{{ $cell }} py-2">{{ $shipping['postal_code'] ?? '—' }}</td>
        </tr>
        <tr>
            <td class="{{ $head }}">ENTREGAS PARCIALES:</td>
            <td class="{{ $head }}">DIAS DE ENTREGA:</td>
        </tr>
        <tr>
            <td class="{{ $cell }} py-2">{{ $shipping['partial_deliveries'] ?? '—' }}</td>
            <td class="{{ $cell }} py-2">{{ $shipping['delivery_days'] ?? '—' }}</td>
        </tr>
        <tr><td class="{{ $head }}" colspan="2">HORARIO DE ENTREGAS:</td></tr>
        <tr><td class="{{ $cell }} py-2" colspan="2">{{ $shipping['delivery_schedule'] ?? '—' }}</td></tr>
        <tr><td class="{{ $head }}" colspan="2">REFERENCIAS:</td></tr>
        <tr><td class="{{ $cell }} py-2 whitespace-pre-line" colspan="2">{{ $shipping['references'] ?? '—' }}</td></tr>
    </table>

    <p class="mb-1 font-bold uppercase">Facturación</p>
    <table class="mb-3 w-full border-collapse">
        <tr><td class="{{ $head }}" colspan="3">RAZON SOCIAL:</td></tr>
        <tr><td class="{{ $cell }} py-2" colspan="3">{{ $billing['legal_name'] ?? '—' }}</td></tr>
        <tr><td class="{{ $head }}" colspan="3">DOMICILIO (CALLE Y NUMERO, COLONIA / PARQUE INDUSTRIAL):</td></tr>
        <tr><td class="{{ $cell }} py-2" colspan="3">{{ $billing['street'] ?? '—' }}</td></tr>
        <tr>
            <td class="{{ $head }}">CIUDAD Y ESTADO:</td>
            <td class="{{ $head }}">R.F.C.:</td>
            <td class="{{ $head }}">C.P:</td>
        </tr>
        <tr>
            <td class="{{ $cell }} py-2">{{ $billing['city_state'] ?? '—' }}</td>
            <td class="{{ $cell }} py-2">{{ $billing['rfc'] ?? '—' }}</td>
            <td class="{{ $cell }} py-2">{{ $billing['postal_code'] ?? '—' }}</td>
        </tr>
        <tr>
            <td class="{{ $head }}">MUB:</td>
            <td class="{{ $head }}">MONEDA:</td>
            <td class="{{ $head }}">TIPO DE CAMBIO:</td>
        </tr>
        <tr>
            <td class="{{ $cell }} py-2">{{ $billing['mub_percent'] ?? '—' }}</td>
            <td class="{{ $cell }} py-2">{{ $billing['currency_code'] ?? '—' }}</td>
            <td class="{{ $cell }} py-2">{{ $billing['exchange_rate'] ?? '—' }}</td>
        </tr>
    </table>

    <p class="mb-1 font-bold uppercase">Condiciones de pago</p>
    <table class="mb-3 w-full border-collapse">
        <tr>
            <td class="{{ $head }}">CONCEPTO</td>
            <td class="{{ $head }}">%</td>
            <td class="{{ $head }}">DIAS</td>
        </tr>
        @foreach ($paymentRows as $row)
            <tr>
                <td class="{{ $cell }} py-1">{{ $row['concept'] }}</td>
                <td class="{{ $cell }} py-1 text-center">{{ $row['percent'] }}</td>
                <td class="{{ $cell }} py-1 text-center">{{ $row['days'] }}</td>
            </tr>
        @endforeach
    </table>

    <p class="mb-1 font-bold uppercase">Información del pedido</p>
    <p class="mb-1 font-semibold uppercase">Fechas de compromiso</p>
    <table class="mb-3 w-full border-collapse">
        <tr>
            <td class="{{ $head }}">TIEMPO DE ENTREGA:</td>
            <td class="{{ $head }}">TIEMPO COMPROMISO ENTREGA:</td>
            <td class="{{ $head }}">FECHA DE INICIO DE INSTALACION:</td>
            <td class="{{ $head }}">FECHA DE PROYECTO:</td>
        </tr>
        <tr>
            <td class="{{ $cell }} py-2">{{ $order['delivery_time'] ?? '—' }}</td>
            <td class="{{ $cell }} py-2">{{ $order['commitment_delivery_date'] ?? 'N/A' }}</td>
            <td class="{{ $cell }} py-2">{{ $order['commitment_installation_start_date'] ?? 'N/A' }}</td>
            <td class="{{ $cell }} py-2">{{ $order['commitment_project_date'] ?? 'N/A' }}</td>
        </tr>
    </table>

    <p class="mb-1 font-bold uppercase">Cliente final</p>
    <table class="mb-3 w-full border-collapse">
        <tr>
            <td class="{{ $head }}">NOMBRE COMERCIAL Y/O RAZON SOCIAL</td>
            <td class="{{ $head }}">CALLE Y NUMERO</td>
        </tr>
        <tr>
            <td class="{{ $cell }} py-2">{{ $final['name'] ?? '—' }}</td>
            <td class="{{ $cell }} py-2">{{ $final['street'] ?? '—' }}</td>
        </tr>
        <tr>
            <td class="{{ $head }}">EJECUTIVO DE CUENTA</td>
            <td class="{{ $head }}">CIUDAD Y ESTADO / C.P.</td>
        </tr>
        <tr>
            <td class="{{ $cell }} py-2">{{ $final['account_executive'] ?? '—' }}</td>
            <td class="{{ $cell }} py-2">
                {{ $final['city_state'] ?? '—' }}
                @if (($final['postal_code'] ?? '—') !== '—')
                    · C.P. {{ $final['postal_code'] }}
                @endif
            </td>
        </tr>
    </table>

    <p class="{{ $head }} border border-gray-300 border-b-0 px-2 py-1 dark:border-white/20">OBSERVACIONES Y RECOMENDACIONES</p>
    <div class="{{ $cell }} mb-3 min-h-20 whitespace-pre-line border-b py-2">
        {{ filled($cover['observations_and_recommendations'] ?? null) ? $cover['observations_and_recommendations'] : '—' }}
    </div>

    @if (filled($cover['fiscal_line'] ?? null) && ($cover['fiscal_line'] ?? '—') !== '—')
        <p class="mb-2 font-semibold">{{ $cover['fiscal_line'] }}</p>
    @endif

    @if (filled($cover['administration_notes'] ?? null))
        <p class="mb-1 font-semibold uppercase">Administración:</p>
        <p class="mb-4 whitespace-pre-line text-[9px] leading-relaxed">{{ $cover['administration_notes'] }}</p>
    @endif

    <div class="mt-8 text-center text-[9px] text-gray-600 dark:text-gray-400">
        <div>{{ $cover['footer_legal'] ?? '' }}</div>
        <div class="mt-2 font-semibold uppercase">{{ $cover['footer_confidential'] ?? '' }}</div>
    </div>
</div>