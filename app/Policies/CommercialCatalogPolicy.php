<?php

namespace App\Policies;

use App\Enums\AppModule;
use App\Models\User;
use App\Support\ModuleAccess;

class CommercialCatalogPolicy
{
    public function viewAny(User $user): bool
    {
        return ModuleAccess::canViewModule($user, AppModule::Comercial);
    }

    public function view(User $user, mixed $record): bool
    {
        return ModuleAccess::canViewModule($user, AppModule::Comercial);
    }

    public function create(User $user): bool
    {
        return ModuleAccess::canEditModule($user, AppModule::Comercial);
    }

    public function update(User $user, mixed $record): bool
    {
        return ModuleAccess::canEditModule($user, AppModule::Comercial);
    }

    public function delete(User $user, mixed $record): bool
    {
        return ModuleAccess::canDeleteInModule($user, AppModule::Comercial);
    }

    public function restore(User $user, mixed $record): bool
    {
        return ModuleAccess::canEditModule($user, AppModule::Comercial);
    }

    public function forceDelete(User $user, mixed $record): bool
    {
        return ModuleAccess::canDeleteInModule($user, AppModule::Comercial);
    }
}
