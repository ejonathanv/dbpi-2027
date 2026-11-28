<?php

namespace App\Support\Ai;

use App\Enums\AppModule;
use App\Models\User;
use App\Support\ModuleAccess;

class AiAgentCatalog
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function categoriesFor(User $user): array
    {
        $categories = config('ai-assistant.agent_categories', []);

        return array_values(array_filter(
            $categories,
            function (array $category) use ($user): bool {
                $module = $category['module'] ?? null;

                if ($module === null) {
                    return true;
                }

                $enum = AppModule::tryFrom((string) $module);

                return $enum !== null && ModuleAccess::canViewModule($user, $enum);
            },
        ));
    }
}
