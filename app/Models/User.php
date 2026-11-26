<?php

namespace App\Models;

use App\Enums\AppModule;
use App\Enums\ModulePermissionLevel;
use App\Enums\UserType;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'user_type' => UserType::class,
            'is_active' => 'boolean',
        ];
    }

    public function moduleMemberships(): HasMany
    {
        return $this->hasMany(ModuleMembership::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active && $panel->getId() === 'admin';
    }

    public function isAdministrator(): bool
    {
        return $this->user_type === UserType::Administrador;
    }

    public function permissionLevelForModule(AppModule $module): ?ModulePermissionLevel
    {
        $membership = $this->moduleMemberships
            ->firstWhere('module', $module);

        return $membership?->permission_level;
    }

    public function hasModulePermission(AppModule $module, ModulePermissionLevel $required): bool
    {
        $level = $this->permissionLevelForModule($module);

        if ($level === null) {
            return false;
        }

        return $level->satisfies($required);
    }

    /**
     * @param  array<string, string|null>  $permissions  module value => permission level value or null
     */
    public function syncModulePermissions(array $permissions): void
    {
        $this->moduleMemberships()->delete();

        foreach ($permissions as $moduleValue => $levelValue) {
            if (blank($levelValue)) {
                continue;
            }

            $this->moduleMemberships()->create([
                'module' => AppModule::from($moduleValue),
                'permission_level' => ModulePermissionLevel::from($levelValue),
            ]);
        }
    }
}