<?php

namespace App\Filament\Resources\HelpdeskServiceAgreements\Pages;

use App\Filament\Resources\HelpdeskServiceAgreements\HelpdeskServiceAgreementResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHelpdeskServiceAgreements extends ListRecords
{
    protected static string $resource = HelpdeskServiceAgreementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nuevo acuerdo SLA'),
        ];
    }
}
