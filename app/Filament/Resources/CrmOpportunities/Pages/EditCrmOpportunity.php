<?php

namespace App\Filament\Resources\CrmOpportunities\Pages;

use App\Filament\Resources\CrmOpportunities\CrmOpportunityResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditCrmOpportunity extends EditRecord
{
    protected static string $resource = CrmOpportunityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
