<?php

namespace App\Filament\Resources\HelpdeskCoverages\Pages;

use App\Filament\Resources\HelpdeskCoverages\HelpdeskCoverageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateHelpdeskCoverage extends CreateRecord
{
    protected static string $resource = HelpdeskCoverageResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
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
