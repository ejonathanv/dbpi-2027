<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Cotización {{ $preview['quote_number'] ?? '' }}</title>
    @include('pdf.commercial-quote.partials.styles')
</head>
<body>
    <div class="mb-4 text-muted">
        @if (filled($preview['issuer_logo_src'] ?? null))
            <img src="{{ $preview['issuer_logo_src'] }}" alt="Dynamic Communications" class="logo mb-2"/>
        @endif
        @foreach ($preview['issuer_address_lines'] ?? [] as $line)
            <div>{{ $line }}</div>
        @endforeach
    </div>

    @include('pdf.commercial-quote.partials.cotizacion-body', ['preview' => $preview, 'money' => $money])
</body>
</html>