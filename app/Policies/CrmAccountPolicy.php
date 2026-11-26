<?php

namespace App\Policies;

use App\Enums\AppModule;
use App\Models\CrmAccount;
use App\Models\User;
use App\Support\ModuleAccess;

class CrmAccountPolicy
{
    public function viewAny(User $user): bool
    {
        return ModuleAccess::canViewModule($user, AppModule::Crm);
    }

    public function view(User $user, CrmAccount $crmAccount): bool
    {
        return ModuleAccess::canViewModule($user, AppModule::Crm);
    }

    public function create(User $user): bool
    {
        return ModuleAccess::canEditModule($user, AppModule::Crm);
    }

    public function update(User $user, CrmAccount $crmAccount): bool
    {
        return ModuleAccess::canEditModule($user, AppModule::Crm);
    }

    public function delete(User $user, CrmAccount $crmAccount): bool
    {
        return ModuleAccess::canDeleteInModule($user, AppModule::Crm);
    }

    public function restore(User $user, CrmAccount $crmAccount): bool
    {
        return ModuleAccess::canEditModule($user, AppModule::Crm);
    }

    public function forceDelete(User $user, CrmAccount $crmAccount): bool
    {
        return ModuleAccess::canDeleteInModule($user, AppModule::Crm);
    }
}
