<?php

namespace App\Policies;

use App\Enums\AppModule;
use App\Models\HelpdeskContract;
use App\Models\User;
use App\Support\ModuleAccess;

class HelpdeskContractPolicy
{
    public function viewAny(User $user): bool
    {
        return ModuleAccess::canViewModule($user, AppModule::Helpdesk);
    }

    public function view(User $user, HelpdeskContract $helpdeskContract): bool
    {
        return ModuleAccess::canViewModule($user, AppModule::Helpdesk);
    }

    public function create(User $user): bool
    {
        return ModuleAccess::canEditModule($user, AppModule::Helpdesk);
    }

    public function update(User $user, HelpdeskContract $helpdeskContract): bool
    {
        return ModuleAccess::canEditModule($user, AppModule::Helpdesk);
    }

    public function delete(User $user, HelpdeskContract $helpdeskContract): bool
    {
        return ModuleAccess::canDeleteInModule($user, AppModule::Helpdesk);
    }

    public function restore(User $user, HelpdeskContract $helpdeskContract): bool
    {
        return ModuleAccess::canEditModule($user, AppModule::Helpdesk);
    }

    public function forceDelete(User $user, HelpdeskContract $helpdeskContract): bool
    {
        return ModuleAccess::canDeleteInModule($user, AppModule::Helpdesk);
    }
}
