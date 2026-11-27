<?php

namespace App\Policies;

use App\Enums\AppModule;
use App\Models\HelpdeskTicket;
use App\Models\User;
use App\Support\ModuleAccess;

class HelpdeskTicketPolicy
{
    public function viewAny(User $user): bool
    {
        return ModuleAccess::canViewModule($user, AppModule::Helpdesk);
    }

    public function view(User $user, HelpdeskTicket $helpdeskTicket): bool
    {
        return ModuleAccess::canViewModule($user, AppModule::Helpdesk);
    }

    public function create(User $user): bool
    {
        return ModuleAccess::canEditModule($user, AppModule::Helpdesk)
            && $user->canCreateHelpdeskTickets();
    }

    public function update(User $user, HelpdeskTicket $helpdeskTicket): bool
    {
        return ModuleAccess::canEditModule($user, AppModule::Helpdesk);
    }

    public function delete(User $user, HelpdeskTicket $helpdeskTicket): bool
    {
        return ModuleAccess::canDeleteInModule($user, AppModule::Helpdesk);
    }

    public function restore(User $user, HelpdeskTicket $helpdeskTicket): bool
    {
        return ModuleAccess::canEditModule($user, AppModule::Helpdesk);
    }

    public function forceDelete(User $user, HelpdeskTicket $helpdeskTicket): bool
    {
        return ModuleAccess::canDeleteInModule($user, AppModule::Helpdesk);
    }
}
