<?php

namespace App\Support;

use App\Enums\AppModule;
use App\Enums\ModulePermissionLevel;
use App\Enums\UserType;
use App\Models\User;

class ModuleAccess
{
    public static function canViewModule(User $user, AppModule $module): bool
    {
        return self::check($user, $module, ModulePermissionLevel::View);
    }

    public static function canEditModule(User $user, AppModule $module): bool
    {
        return self::check($user, $module, ModulePermissionLevel::Edit);
    }

    public static function canDeleteInModule(User $user, AppModule $module): bool
    {
        return self::check($user, $module, ModulePermissionLevel::Delete);
    }

    public static function check(User $user, AppModule $module, ModulePermissionLevel $required): bool
    {
        if (! $user->is_active) {
            return false;
        }

        if ($user->isAdministrator()) {
            return true;
        }

        if ($user->user_type === UserType::Contacto) {
            return false;
        }

        if ($user->user_type !== UserType::Usuario) {
            return false;
        }

        return $user->hasModulePermission($module, $required);
    }
}