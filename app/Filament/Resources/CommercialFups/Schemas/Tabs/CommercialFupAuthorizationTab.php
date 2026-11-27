<?php

namespace App\Filament\Resources\CommercialFups\Schemas\Tabs;

use App\Models\CommercialFup;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\ViewField;

class CommercialFupAuthorizationTab
{
    /** @return array<int, mixed> */
    public static function tabSchema(string $operation): array
    {
        if ($operation !== 'edit') {
            return [
                Placeholder::make('fup_auth_requires_save')
                    ->content('Guarde la FUP para ver autorizaciones.'),
            ];
        }

        return [
            ViewField::make('fup_autorizaciones')
                ->hiddenLabel()
                ->dehydrated(false)
                ->view('filament.resources.commercial-fups.authorization-tab')
                ->viewData(fn (?CommercialFup $record): array => [
                    'fup' => $record,
                ]),
        ];
    }
}