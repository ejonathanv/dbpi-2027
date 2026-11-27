<?php

namespace App\Filament\Resources\CommercialQuotes\Schemas;

use App\Filament\Resources\CommercialQuotes\Pages\EditCommercialQuote;
use App\Filament\Resources\CommercialQuotes\RelationManagers\CommercialQuoteSummaryPanel;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Livewire;

class CommercialQuoteSummaryTab
{
    /** @return array<int, mixed> */
    public static function tabSchema(string $operation): array
    {
        if ($operation !== 'edit') {
            return [
                Placeholder::make('resumen_requires_save')
                    ->label('')
                    ->content('Guarde la cotización para ver la vista previa.'),
            ];
        }

        return [
            Livewire::make(CommercialQuoteSummaryPanel::class, fn (Livewire $component): array => [
                'ownerRecord' => $component->getRecord(),
                'pageClass' => EditCommercialQuote::class,
            ])->lazyLoaded(),
        ];
    }
}
