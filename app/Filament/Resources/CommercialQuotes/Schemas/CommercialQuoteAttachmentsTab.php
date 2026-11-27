<?php

namespace App\Filament\Resources\CommercialQuotes\Schemas;

use App\Filament\Resources\CommercialQuotes\Pages\EditCommercialQuote;
use App\Filament\Resources\CommercialQuotes\RelationManagers\CommercialQuoteAttachmentsPanel;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Livewire;

class CommercialQuoteAttachmentsTab
{
    /** @return array<int, mixed> */
    public static function tabSchema(string $operation): array
    {
        if ($operation !== 'edit') {
            return [
                Placeholder::make('adjuntos_requires_save')
                    ->content('Guarde la cotización para gestionar archivos adjuntos.'),
            ];
        }

        return [
            Livewire::make(CommercialQuoteAttachmentsPanel::class, fn (Livewire $component): array => [
                'ownerRecord' => $component->getRecord(),
                'pageClass' => EditCommercialQuote::class,
            ])->lazyLoaded(),
        ];
    }
}