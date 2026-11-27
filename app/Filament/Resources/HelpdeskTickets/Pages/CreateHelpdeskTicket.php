<?php

namespace App\Filament\Resources\HelpdeskTickets\Pages;

use App\Enums\HelpdeskTicketOpenedByType;
use App\Enums\HelpdeskTicketStatus;
use App\Filament\Resources\HelpdeskTickets\HelpdeskTicketResource;
use App\Models\Employee;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;

class CreateHelpdeskTicket extends CreateRecord
{
    protected static string $resource = HelpdeskTicketResource::class;

    public function mount(): void
    {
        parent::mount();

        $user = auth()->user();

        if (! $user instanceof User || ! $user->shouldAutoAssignHelpdeskTicketOnCreate()) {
            return;
        }

        $employee = Employee::query()->find($user->helpdesk_employee_id);

        if (! $employee) {
            return;
        }

        $this->form->fill([
            'company_id' => $employee->company_id,
            'company_department_id' => $employee->company_department_id,
            'employee_id' => $employee->getKey(),
        ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = HelpdeskTicketStatus::Abierto->value;
        $data['opened_by_type'] = HelpdeskTicketOpenedByType::User->value;
        $data['opened_by_user_id'] = auth()->id();
        $data['opened_by_contact_id'] = null;

        $user = auth()->user();

        if ($user instanceof User && $user->shouldAutoAssignHelpdeskTicketOnCreate()) {
            $employee = Employee::query()->find($user->helpdesk_employee_id);

            if ($employee) {
                $data['company_id'] = $employee->company_id;
                $data['company_department_id'] = $employee->company_department_id;
                $data['employee_id'] = $employee->getKey();
            }
        } elseif ($user instanceof User && ! $user->shouldAutoAssignHelpdeskTicketOnCreate()) {
            $data['employee_id'] = null;
        }

        return $data;
    }
}
