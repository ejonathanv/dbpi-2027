<?php

namespace App\Policies;

use App\Enums\AppModule;
use App\Models\HelpdeskCategory;
use App\Models\User;
use App\Support\ModuleAccess;

class HelpdeskCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return ModuleAccess::canViewModule($user, AppModule::Helpdesk);
    }

    public function view(User $user, HelpdeskCategory $helpdeskCategory): bool
    {
        return ModuleAccess::canViewModule($user, AppModule::Helpdesk);
    }

    public function create(User $user): bool
    {
        return ModuleAccess::canEditModule($user, AppModule::Helpdesk);
    }

    public function update(User $user, HelpdeskCategory $helpdeskCategory): bool
    {
        return ModuleAccess::canEditModule($user, AppModule::Helpdesk);
    }

    public function delete(User $user, HelpdeskCategory $helpdeskCategory): bool
    {
        return ModuleAccess::canDeleteInModule($user, AppModule::Helpdesk);
    }
}
