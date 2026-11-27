<?php

namespace App\Policies;

use App\Enums\AppModule;
use App\Models\HelpdeskServiceAgreement;
use App\Models\User;
use App\Support\ModuleAccess;

class HelpdeskServiceAgreementPolicy
{
    public function viewAny(User $user): bool
    {
        return ModuleAccess::canViewModule($user, AppModule::Helpdesk);
    }

    public function view(User $user, HelpdeskServiceAgreement $helpdeskServiceAgreement): bool
    {
        return ModuleAccess::canViewModule($user, AppModule::Helpdesk);
    }

    public function create(User $user): bool
    {
        return ModuleAccess::canEditModule($user, AppModule::Helpdesk);
    }

    public function update(User $user, HelpdeskServiceAgreement $helpdeskServiceAgreement): bool
    {
        return ModuleAccess::canEditModule($user, AppModule::Helpdesk);
    }

    public function delete(User $user, HelpdeskServiceAgreement $helpdeskServiceAgreement): bool
    {
        return ModuleAccess::canDeleteInModule($user, AppModule::Helpdesk);
    }

    public function restore(User $user, HelpdeskServiceAgreement $helpdeskServiceAgreement): bool
    {
        return ModuleAccess::canEditModule($user, AppModule::Helpdesk);
    }

    public function forceDelete(User $user, HelpdeskServiceAgreement $helpdeskServiceAgreement): bool
    {
        return ModuleAccess::canDeleteInModule($user, AppModule::Helpdesk);
    }
}
