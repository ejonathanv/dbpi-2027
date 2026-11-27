<?php

namespace App\Support;

use App\Models\CommercialQuote;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CommercialQuotePdfExporter
{
    public const VARIANT_COTIZACION = 'cotizacion';

    public const VARIANT_MARGEN = 'margen';

    public const VARIANT_TODO = 'todo';

    public static function download(CommercialQuote $quote, string $variant): Response
    {
        $variant = self::normalizeVariant($variant);

        $filename = self::filename($quote, $variant);

        $pdf = match ($variant) {
            self::VARIANT_COTIZACION => self::pdfCotizacion($quote),
            self::VARIANT_MARGEN => self::pdfMargen($quote),
            self::VARIANT_TODO => self::pdfTodo($quote),
        };

        return $pdf->download($filename);
    }

    /**
     * @return array<string, mixed>
     */
    public static function previewForQuote(CommercialQuote $quote): array
    {
        $quote->loadMissing(['account', 'contact']);

        if ($quote->isEditingLocked() && filled($quote->frozen_snapshot['preview'] ?? null)) {
            $preview = (array) $quote->frozen_snapshot['preview'];
        } else {
            $preview = CommercialQuotePreviewBuilder::forQuote($quote);
        }

        $preview['issuer_logo_src'] = self::imageSrcForPdf($preview['issuer_logo_url'] ?? null);
        $preview['customer_logo_src'] = self::imageSrcForPdf($preview['customer_logo_url'] ?? null);

        return $preview;
    }

    protected static function normalizeVariant(string $variant): string
    {
        return match ($variant) {
            self::VARIANT_COTIZACION, self::VARIANT_MARGEN, self::VARIANT_TODO => $variant,
            default => abort(404),
        };
    }

    protected static function filename(CommercialQuote $quote, string $variant): string
    {
        $slug = Str::slug((string) ($quote->quote_number ?? 'cotizacion'));

        $suffix = match ($variant) {
            self::VARIANT_COTIZACION => 'cotizacion',
            self::VARIANT_MARGEN => 'margen',
            self::VARIANT_TODO => 'completo',
        };

        return sprintf('%s-%s.pdf', $slug, $suffix);
    }

    protected static function pdfCotizacion(CommercialQuote $quote): \Barryvdh\DomPDF\PDF
    {
        return Pdf::loadView('pdf.commercial-quote.cotizacion', [
            'preview' => self::previewForQuote($quote),
            'money' => self::moneyFormatter(),
        ])->setPaper('letter', 'portrait');
    }

    protected static function pdfMargen(CommercialQuote $quote): \Barryvdh\DomPDF\PDF
    {
        return Pdf::loadView('pdf.commercial-quote.margen', [
            'report' => CommercialQuoteMarginReport::forQuote($quote),
            'money' => self::moneyFormatter(),
            'percent' => self::percentFormatter(),
        ])->setPaper('letter', 'landscape');
    }

    protected static function pdfTodo(CommercialQuote $quote): \Barryvdh\DomPDF\PDF
    {
        return Pdf::loadView('pdf.commercial-quote.todo', [
            'preview' => self::previewForQuote($quote),
            'report' => CommercialQuoteMarginReport::forQuote($quote),
            'money' => self::moneyFormatter(),
            'percent' => self::percentFormatter(),
        ])->setPaper('letter', 'portrait');
    }

    protected static function moneyFormatter(): \Closure
    {
        return static fn (float $value): string => '$'.number_format($value, 2, '.', ',');
    }

    protected static function percentFormatter(): \Closure
    {
        return static fn (float $value): string => number_format($value, 2, '.', ',').'%';
    }

    protected static function imageSrcForPdf(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        $path = self::localPathFromUrl($url);

        if ($path === null || ! is_readable($path)) {
            return $url;
        }

        $mime = mime_content_type($path) ?: 'image/png';
        $contents = file_get_contents($path);

        if ($contents === false) {
            return $url;
        }

        return 'data:'.$mime.';base64,'.base64_encode($contents);
    }

    protected static function localPathFromUrl(string $url): ?string
    {
        if (str_starts_with($url, '/storage/')) {
            $relative = Str::after($url, '/storage/');

            return Storage::disk('public')->path($relative);
        }

        $publicPath = parse_url($url, PHP_URL_PATH);

        if (is_string($publicPath) && str_starts_with($publicPath, '/')) {
            $candidate = public_path(ltrim($publicPath, '/'));

            if (is_file($candidate)) {
                return $candidate;
            }
        }

        $assetPath = Str::after($url, url('/'));

        if (filled($assetPath)) {
            $candidate = public_path(ltrim($assetPath, '/'));

            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}