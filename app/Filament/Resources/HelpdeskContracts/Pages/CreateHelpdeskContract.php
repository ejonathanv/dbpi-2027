<?php

namespace App\Filament\Resources\HelpdeskContracts\Pages;

use App\Filament\Resources\HelpdeskContracts\HelpdeskContractResource;
use Filament\Resources\Pages\CreateRecord;

class CreateHelpdeskContract extends CreateRecord
{
    protected static string $resource = HelpdeskContractResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
