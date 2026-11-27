<?php

namespace App\Policies;

use App\Enums\AppModule;
use App\Models\CommercialQuote;
use App\Models\User;
use App\Support\ModuleAccess;

class CommercialQuotePolicy
{
    public function viewAny(User $user): bool
    {
        return ModuleAccess::canViewModule($user, AppModule::Comercial);
    }

    public function view(User $user, CommercialQuote $commercialQuote): bool
    {
        return ModuleAccess::canViewModule($user, AppModule::Comercial);
    }

    public function create(User $user): bool
    {
        return ModuleAccess::canEditModule($user, AppModule::Comercial);
    }

    public function update(User $user, CommercialQuote $commercialQuote): bool
    {
        return ModuleAccess::canEditModule($user, AppModule::Comercial);
    }

    public function delete(User $user, CommercialQuote $commercialQuote): bool
    {
        return ModuleAccess::canDeleteInModule($user, AppModule::Comercial);
    }

    public function restore(User $user, CommercialQuote $commercialQuote): bool
    {
        return ModuleAccess::canEditModule($user, AppModule::Comercial);
    }

    public function forceDelete(User $user, CommercialQuote $commercialQuote): bool
    {
        return ModuleAccess::canDeleteInModule($user, AppModule::Comercial);
    }
}
