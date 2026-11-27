<?php

namespace App\Filament\Resources\CommercialSuppliers\Pages;

use App\Filament\Resources\CommercialSuppliers\CommercialSupplierResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCommercialSuppliers extends ListRecords
{
    protected static string $resource = CommercialSupplierResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Nuevo proveedor')];
    }
}
