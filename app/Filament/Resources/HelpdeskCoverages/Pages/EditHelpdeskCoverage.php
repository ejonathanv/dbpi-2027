<?php

namespace App\Filament\Resources\HelpdeskCoverages\Pages;

use App\Filament\Resources\HelpdeskCoverages\HelpdeskCoverageResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditHelpdeskCoverage extends EditRecord
{
    protected static string $resource = HelpdeskCoverageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($data['full_week'] ?? false) {
            $data['weekdays'] = null;
        } elseif (filled($data['weekdays'] ?? null)) {
            $data['weekdays'] = array_map(intval(...), (array) $data['weekdays']);
        }

        if ($data['all_day'] ?? false) {
            $data['coverage_start'] = null;
            $data['coverage_end'] = null;
        }

        return $data;
    }
}
