<?php

namespace App\Policies;

use App\Enums\AppModule;
use App\Models\HelpdeskCoverage;
use App\Models\User;
use App\Support\ModuleAccess;

class HelpdeskCoveragePolicy
{
    public function viewAny(User $user): bool
    {
        return ModuleAccess::canViewModule($user, AppModule::Helpdesk);
    }

    public function view(User $user, HelpdeskCoverage $helpdeskCoverage): bool
    {
        return ModuleAccess::canViewModule($user, AppModule::Helpdesk);
    }

    public function create(User $user): bool
    {
        return ModuleAccess::canEditModule($user, AppModule::Helpdesk);
    }

    public function update(User $user, HelpdeskCoverage $helpdeskCoverage): bool
    {
        return ModuleAccess::canEditModule($user, AppModule::Helpdesk);
    }

    public function delete(User $user, HelpdeskCoverage $helpdeskCoverage): bool
    {
        return ModuleAccess::canDeleteInModule($user, AppModule::Helpdesk);
    }

    public function restore(User $user, HelpdeskCoverage $helpdeskCoverage): bool
    {
        return ModuleAccess::canEditModule($user, AppModule::Helpdesk);
    }

    public function forceDelete(User $user, HelpdeskCoverage $helpdeskCoverage): bool
    {
        return ModuleAccess::canDeleteInModule($user, AppModule::Helpdesk);
    }
}
