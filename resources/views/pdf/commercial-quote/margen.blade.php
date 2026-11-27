<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Margen · {{ $report['quote_number'] }}</title>
    @include('pdf.commercial-quote.partials.styles')
</head>
<body>
    @include('pdf.commercial-quote.partials.margen-body', [
        'report' => $report,
        'money' => $money,
        'percent' => $percent,
    ])
</body>
</html>