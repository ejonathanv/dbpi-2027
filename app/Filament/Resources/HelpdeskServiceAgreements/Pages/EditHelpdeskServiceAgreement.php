<?php

namespace App\Filament\Resources\HelpdeskServiceAgreements\Pages;

use App\Filament\Resources\HelpdeskServiceAgreements\HelpdeskServiceAgreementResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditHelpdeskServiceAgreement extends EditRecord
{
    protected static string $resource = HelpdeskServiceAgreementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
