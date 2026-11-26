<?php

namespace App\Filament\Resources\CrmCalendarEvents\Pages;

use App\Filament\Resources\CrmCalendarEvents\CrmCalendarEventResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCrmCalendarEvent extends EditRecord
{
    protected static string $resource = CrmCalendarEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
