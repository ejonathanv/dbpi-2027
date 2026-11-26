<?php

namespace App\Policies;

use App\Enums\AppModule;
use App\Models\Company;
use App\Models\User;
use App\Support\ModuleAccess;

class CompanyPolicy
{
    public function viewAny(User $user): bool
    {
        if (! ModuleAccess::canViewModule($user, AppModule::Rh)) {
            return false;
        }

        return $user->hasUnrestrictedRhCompanyAccess()
            || $user->companies()->exists();
    }

    public function view(User $user, Company $company): bool
    {
        return ModuleAccess::canViewModule($user, AppModule::Rh)
            && $user->canAccessCompany($company);
    }

    public function create(User $user): bool
    {
        return ModuleAccess::canEditModule($user, AppModule::Rh);
    }

    public function update(User $user, Company $company): bool
    {
        return ModuleAccess::canEditModule($user, AppModule::Rh)
            && $user->canAccessCompany($company);
    }

    public function delete(User $user, Company $company): bool
    {
        return ModuleAccess::canDeleteInModule($user, AppModule::Rh)
            && $user->canAccessCompany($company);
    }

    public function restore(User $user, Company $company): bool
    {
        return ModuleAccess::canEditModule($user, AppModule::Rh)
            && $user->canAccessCompany($company);
    }

    public function forceDelete(User $user, Company $company): bool
    {
        return ModuleAccess::canDeleteInModule($user, AppModule::Rh)
            && $user->canAccessCompany($company);
    }
}