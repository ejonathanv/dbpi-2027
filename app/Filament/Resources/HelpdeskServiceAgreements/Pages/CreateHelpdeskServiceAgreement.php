<?php

namespace App\Filament\Resources\HelpdeskServiceAgreements\Pages;

use App\Filament\Resources\HelpdeskServiceAgreements\HelpdeskServiceAgreementResource;
use Filament\Resources\Pages\CreateRecord;

class CreateHelpdeskServiceAgreement extends CreateRecord
{
    protected static string $resource = HelpdeskServiceAgreementResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
