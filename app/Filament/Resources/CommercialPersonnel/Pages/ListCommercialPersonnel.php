<?php

namespace App\Filament\Resources\CommercialPersonnel\Pages;

use App\Filament\Resources\CommercialPersonnel\CommercialPersonnelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCommercialPersonnel extends ListRecords
{
    protected static string $resource = CommercialPersonnelResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Nuevo recurso')];
    }
}
