<?php

namespace App\Filament\Resources\HelpdeskContracts\Pages;

use App\Filament\Resources\HelpdeskContracts\HelpdeskContractResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHelpdeskContracts extends ListRecords
{
    protected static string $resource = HelpdeskContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nuevo contrato'),
        ];
    }
}
