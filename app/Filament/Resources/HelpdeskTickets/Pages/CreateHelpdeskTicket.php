<?php

namespace App\Filament\Resources\HelpdeskTickets\Pages;

use App\Enums\HelpdeskTicketOpenedByType;
use App\Enums\HelpdeskTicketStatus;
use App\Filament\Resources\HelpdeskTickets\HelpdeskTicketResource;
use Filament\Resources\Pages\CreateRecord;

class CreateHelpdeskTicket extends CreateRecord
{
    protected static string $resource = HelpdeskTicketResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = HelpdeskTicketStatus::Abierto->value;
        $data['opened_by_type'] = HelpdeskTicketOpenedByType::User->value;
        $data['opened_by_user_id'] = auth()->id();
        $data['opened_by_contact_id'] = null;

        return $data;
    }
}
