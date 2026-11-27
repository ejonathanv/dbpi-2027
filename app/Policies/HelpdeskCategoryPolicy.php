<?php

namespace App\Policies;

use App\Models\HelpdeskCategory;
use App\Models\User;

class HelpdeskCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdministrator();
    }

    public function view(User $user, HelpdeskCategory $helpdeskCategory): bool
    {
        return $user->isAdministrator();
    }

    public function create(User $user): bool
    {
        return $user->isAdministrator();
    }

    public function update(User $user, HelpdeskCategory $helpdeskCategory): bool
    {
        return $user->isAdministrator();
    }

    public function delete(User $user, HelpdeskCategory $helpdeskCategory): bool
    {
        return $user->isAdministrator();
    }
}
