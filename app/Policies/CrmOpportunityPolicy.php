<?php

namespace App\Policies;

use App\Enums\AppModule;
use App\Models\CrmOpportunity;
use App\Models\User;
use App\Support\ModuleAccess;

class CrmOpportunityPolicy
{
    public function viewAny(User $user): bool
    {
        return ModuleAccess::canViewModule($user, AppModule::Crm);
    }

    public function view(User $user, CrmOpportunity $crmOpportunity): bool
    {
        return ModuleAccess::canViewModule($user, AppModule::Crm);
    }

    public function create(User $user): bool
    {
        return ModuleAccess::canEditModule($user, AppModule::Crm);
    }

    public function update(User $user, CrmOpportunity $crmOpportunity): bool
    {
        return ModuleAccess::canEditModule($user, AppModule::Crm);
    }

    public function delete(User $user, CrmOpportunity $crmOpportunity): bool
    {
        return ModuleAccess::canDeleteInModule($user, AppModule::Crm);
    }

    public function restore(User $user, CrmOpportunity $crmOpportunity): bool
    {
        return ModuleAccess::canEditModule($user, AppModule::Crm);
    }

    public function forceDelete(User $user, CrmOpportunity $crmOpportunity): bool
    {
        return ModuleAccess::canDeleteInModule($user, AppModule::Crm);
    }
}
