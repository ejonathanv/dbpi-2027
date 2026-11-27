<?php

namespace App\Filament\Resources\CommercialPersonnel\Pages;

use App\Filament\Resources\CommercialPersonnel\CommercialPersonnelResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditCommercialPersonnel extends EditRecord
{
    protected static string $resource = CommercialPersonnelResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make(), ForceDeleteAction::make(), RestoreAction::make()];
    }
}
