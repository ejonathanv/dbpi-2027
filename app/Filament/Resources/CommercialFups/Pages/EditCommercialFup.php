<?php

namespace App\Filament\Resources\CommercialFups\Pages;

use App\Filament\Resources\CommercialFups\CommercialFupResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditCommercialFup extends EditRecord
{
    protected static string $resource = CommercialFupResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($this->getRecord()->isEditingLocked()) {
            return [
                'observations_and_recommendations' => $data['observations_and_recommendations'] ?? null,
            ];
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make(), ForceDeleteAction::make(), RestoreAction::make()];
    }
}
