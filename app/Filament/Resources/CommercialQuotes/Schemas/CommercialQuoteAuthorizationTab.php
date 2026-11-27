<?php

namespace App\Filament\Resources\CommercialQuotes\Schemas;

use App\Models\CommercialQuote;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\ViewField;

class CommercialQuoteAuthorizationTab
{
    /** @return array<int, mixed> */
    public static function tabSchema(string $operation): array
    {
        if ($operation !== 'edit') {
            return [
                Placeholder::make('auth_requires_save')
                    ->hiddenLabel()
                    ->content('Guarde la cotización para ver el historial de autorizaciones.'),
            ];
        }

        return [
            ViewField::make('panel_autorizaciones')
                ->hiddenLabel()
                ->dehydrated(false)
                ->view('filament.resources.commercial-quotes.authorization-tab')
                ->viewData(fn (?CommercialQuote $record): array => [
                    'quote' => $record,
                ]),
        ];
    }
}
