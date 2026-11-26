<?php

namespace App\Policies;

use App\Enums\AppModule;
use App\Models\CrmCalendarEvent;
use App\Models\User;
use App\Support\ModuleAccess;

class CrmCalendarEventPolicy
{
    public function viewAny(User $user): bool
    {
        return ModuleAccess::canViewModule($user, AppModule::Crm);
    }

    public function view(User $user, CrmCalendarEvent $crmCalendarEvent): bool
    {
        return ModuleAccess::canViewModule($user, AppModule::Crm);
    }

    public function create(User $user): bool
    {
        return ModuleAccess::canEditModule($user, AppModule::Crm);
    }

    public function update(User $user, CrmCalendarEvent $crmCalendarEvent): bool
    {
        return ModuleAccess::canEditModule($user, AppModule::Crm);
    }

    public function delete(User $user, CrmCalendarEvent $crmCalendarEvent): bool
    {
        return ModuleAccess::canDeleteInModule($user, AppModule::Crm);
    }
}
