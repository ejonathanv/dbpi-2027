<?php

namespace App\Filament\Resources\HelpdeskCoverages\Pages;

use App\Filament\Resources\HelpdeskCoverages\HelpdeskCoverageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHelpdeskCoverages extends ListRecords
{
    protected static string $resource = HelpdeskCoverageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nueva cobertura'),
        ];
    }
}
