<?php

namespace App\Filament\Resources\CrmCalendarEvents\Pages;

use App\Filament\Resources\CrmCalendarEvents\CrmCalendarEventResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCrmCalendarEvents extends ListRecords
{
    protected static string $resource = CrmCalendarEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Nuevo evento'),
        ];
    }
}
