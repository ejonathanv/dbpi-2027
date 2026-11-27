<?php

namespace App\Filament\Resources\CommercialQuotes\Pages;

use App\Filament\Resources\CommercialQuotes\CommercialQuoteResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditCommercialQuote extends EditRecord
{
    protected static string $resource = CommercialQuoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
