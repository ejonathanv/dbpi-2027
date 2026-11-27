<?php

namespace App\Support;

use App\Filament\Resources\CommercialQuotes\CommercialQuoteResource;
use App\Models\CommercialQuote;

final class CommercialQuotePanelUrls
{
    public const EDIT_TAB_AUTORIZACIONES = 'autorizaciones';

    public static function editAuthorizationTab(CommercialQuote $quote): string
    {
        return CommercialQuoteResource::getUrl('edit', [
            'record' => $quote,
        ]).'?tab='.self::EDIT_TAB_AUTORIZACIONES;
    }

    public static function downloadPdf(CommercialQuote $quote, string $variant): string
    {
        return CommercialQuoteResource::getUrl('download-pdf', [
            'record' => $quote,
            'variant' => $variant,
        ]);
    }
}