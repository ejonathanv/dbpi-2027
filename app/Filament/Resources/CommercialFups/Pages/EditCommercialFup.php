<?php

namespace App\Filament\Resources\CommercialFups\Pages;

use App\Filament\Resources\CommercialFups\CommercialFupResource;
use App\Support\CommercialFupCoverBuilder;
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
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();

        if (blank($record->cover_payload)) {
            $data['cover_payload'] = CommercialFupCoverBuilder::defaultPayload($record);

            return $data;
        }

        if (blank(data_get($record->cover_payload, 'billing.mub_percent'))) {
            $defaults = CommercialFupCoverBuilder::defaultPayload($record);
            data_set($data, 'cover_payload.billing.mub_percent', $defaults['billing']['mub_percent'] ?? '');
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($this->getRecord()->isEditingLocked()) {
            return [];
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make(), ForceDeleteAction::make(), RestoreAction::make()];
    }
}
