<?php

namespace App\Policies;

use App\Enums\AppModule;
use App\Models\CrmContact;
use App\Models\User;
use App\Support\ModuleAccess;

class CrmContactPolicy
{
    public function viewAny(User $user): bool
    {
        return ModuleAccess::canViewModule($user, AppModule::Crm);
    }

    public function view(User $user, CrmContact $crmContact): bool
    {
        return ModuleAccess::canViewModule($user, AppModule::Crm);
    }

    public function create(User $user): bool
    {
        return ModuleAccess::canEditModule($user, AppModule::Crm);
    }

    public function update(User $user, CrmContact $crmContact): bool
    {
        return ModuleAccess::canEditModule($user, AppModule::Crm);
    }

    public function delete(User $user, CrmContact $crmContact): bool
    {
        return ModuleAccess::canDeleteInModule($user, AppModule::Crm);
    }

    public function restore(User $user, CrmContact $crmContact): bool
    {
        return ModuleAccess::canEditModule($user, AppModule::Crm);
    }

    public function forceDelete(User $user, CrmContact $crmContact): bool
    {
        return ModuleAccess::canDeleteInModule($user, AppModule::Crm);
    }
}
