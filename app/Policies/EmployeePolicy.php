<?php

namespace App\Policies;

use App\Enums\AppModule;
use App\Models\Employee;
use App\Models\User;
use App\Support\ModuleAccess;

class EmployeePolicy
{
    public function viewAny(User $user): bool
    {
        if (! ModuleAccess::canViewModule($user, AppModule::Rh)) {
            return false;
        }

        return $user->hasUnrestrictedRhCompanyAccess()
            || $user->companies()->exists();
    }

    public function view(User $user, Employee $employee): bool
    {
        return ModuleAccess::canViewModule($user, AppModule::Rh)
            && $user->canAccessCompany($employee->company);
    }

    public function create(User $user): bool
    {
        return ModuleAccess::canEditModule($user, AppModule::Rh);
    }

    public function update(User $user, Employee $employee): bool
    {
        return ModuleAccess::canEditModule($user, AppModule::Rh)
            && $user->canAccessCompany($employee->company);
    }

    public function delete(User $user, Employee $employee): bool
    {
        return ModuleAccess::canDeleteInModule($user, AppModule::Rh)
            && $user->canAccessCompany($employee->company);
    }

    public function restore(User $user, Employee $employee): bool
    {
        return ModuleAccess::canEditModule($user, AppModule::Rh)
            && $user->canAccessCompany($employee->company);
    }

    public function forceDelete(User $user, Employee $employee): bool
    {
        return ModuleAccess::canDeleteInModule($user, AppModule::Rh)
            && $user->canAccessCompany($employee->company);
    }
}