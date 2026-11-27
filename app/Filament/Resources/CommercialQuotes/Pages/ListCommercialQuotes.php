<?php

namespace App\Filament\Resources\CommercialQuotes\Pages;

use App\Filament\Resources\CommercialQuotes\CommercialQuoteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCommercialQuotes extends ListRecords
{
    protected static string $resource = CommercialQuoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nueva cotización'),
        ];
    }
}
