<?php

namespace App\Filament\Resources\CrmAccounts\Pages;

use App\Filament\Resources\CrmAccounts\CrmAccountResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditCrmAccount extends EditRecord
{
    protected static string $resource = CrmAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
