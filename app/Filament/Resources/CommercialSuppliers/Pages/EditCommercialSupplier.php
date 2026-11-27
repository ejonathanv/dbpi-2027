<?php

namespace App\Filament\Resources\CommercialSuppliers\Pages;

use App\Filament\Resources\CommercialSuppliers\CommercialSupplierResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditCommercialSupplier extends EditRecord
{
    protected static string $resource = CommercialSupplierResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make(), ForceDeleteAction::make(), RestoreAction::make()];
    }
}
