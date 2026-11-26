<?php

namespace App\Filament\Resources\Users\Concerns;

use App\Enums\AppModule;
use App\Enums\UserType;

trait SyncsUserModulePermissions
{
    /** @var array<string, string|null> */
    protected array $modulePermissions = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['module_permissions'] = [];

        foreach (AppModule::cases() as $module) {
            $level = $this->record->permissionLevelForModule($module);
            $data['module_permissions'][$module->value] = $level?->value;
        }

        return $data;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->modulePermissions = $data['module_permissions'] ?? [];
        unset($data['module_permissions']);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->modulePermissions = $data['module_permissions'] ?? [];
        unset($data['module_permissions']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->syncPermissionsOnRecord();
    }

    protected function afterSave(): void
    {
        $this->syncPermissionsOnRecord();
    }

    protected function syncPermissionsOnRecord(): void
    {
        if ($this->record->user_type === UserType::Usuario) {
            $this->record->syncModulePermissions($this->modulePermissions);
        } else {
            $this->record->moduleMemberships()->delete();
        }
    }
}