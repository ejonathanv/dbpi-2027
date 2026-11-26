<?php

namespace App\Filament\Resources\Users\Concerns;

use App\Enums\AppModule;
use App\Enums\UserType;

trait SyncsUserModulePermissions
{
    /** @var array<string, string|null> */
    protected array $modulePermissions = [];

    /** @var array<int|string> */
    protected array $companyIds = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['user_type'] = $this->record->user_type?->value;

        $data['module_permissions'] = [];

        foreach (AppModule::cases() as $module) {
            $level = $this->record->permissionLevelForModule($module);
            $data['module_permissions'][$module->value] = $level?->value;
        }

        $data['company_ids'] = $this->record->companies()->pluck('companies.id')->all();

        return $data;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->modulePermissions = $this->normalizeModulePermissions($data['module_permissions'] ?? []);
        $this->companyIds = $data['company_ids'] ?? [];
        unset($data['module_permissions'], $data['company_ids']);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->modulePermissions = $this->normalizeModulePermissions($data['module_permissions'] ?? []);
        $this->companyIds = $data['company_ids'] ?? [];
        unset($data['module_permissions'], $data['company_ids']);

        return $data;
    }

    /**
     * @param  array<string, mixed>  $permissions
     * @return array<string, string|null>
     */
    protected function normalizeModulePermissions(array $permissions): array
    {
        $normalized = [];

        foreach (AppModule::cases() as $module) {
            $level = $permissions[$module->value] ?? null;

            if ($level instanceof \BackedEnum) {
                $level = $level->value;
            }

            $normalized[$module->value] = filled($level) ? (string) $level : null;
        }

        return $normalized;
    }

    protected function afterCreate(): void
    {
        $this->syncPermissionsOnRecord();
        $this->syncCompanyAccessOnRecord();
    }

    protected function afterSave(): void
    {
        $this->syncPermissionsOnRecord();
        $this->syncCompanyAccessOnRecord();
    }

    protected function syncPermissionsOnRecord(): void
    {
        if ($this->record->user_type === UserType::Usuario) {
            $this->record->syncModulePermissions($this->modulePermissions);
        } else {
            $this->record->moduleMemberships()->delete();
        }
    }

    protected function syncCompanyAccessOnRecord(): void
    {
        if ($this->record->user_type !== UserType::Usuario) {
            $this->record->companies()->detach();

            return;
        }

        $rhLevel = $this->modulePermissions[AppModule::Rh->value] ?? null;

        if (blank($rhLevel)) {
            $this->record->companies()->detach();

            return;
        }

        $this->record->syncCompanyAccess($this->companyIds);
    }
}