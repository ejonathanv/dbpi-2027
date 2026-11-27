<?php

namespace App\Models;

use App\Enums\AppModule;
use App\Enums\CrmRecordScope;
use App\Enums\HelpdeskTicketScope;
use App\Enums\ModulePermissionLevel;
use App\Enums\UserType;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'crm_record_scope',
        'can_create_helpdesk_tickets',
        'helpdesk_auto_assign_on_create',
        'helpdesk_employee_id',
        'helpdesk_ticket_scope',
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
            'crm_record_scope' => CrmRecordScope::class,
            'can_create_helpdesk_tickets' => 'boolean',
            'helpdesk_auto_assign_on_create' => 'boolean',
            'helpdesk_ticket_scope' => HelpdeskTicketScope::class,
        ];
    }

    public function helpdeskEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'helpdesk_employee_id');
    }

    public function canCreateHelpdeskTickets(): bool
    {
        if ($this->isAdministrator()) {
            return true;
        }

        if (! $this->hasModulePermission(AppModule::Helpdesk, ModulePermissionLevel::Edit)) {
            return false;
        }

        return $this->can_create_helpdesk_tickets;
    }

    public function shouldAutoAssignHelpdeskTicketOnCreate(): bool
    {
        if (! $this->helpdesk_auto_assign_on_create) {
            return false;
        }

        return $this->helpdesk_employee_id !== null;
    }

    public function restrictsCrmToOwnRecords(): bool
    {
        return $this->crm_record_scope === CrmRecordScope::Own;
    }

    public function moduleMemberships(): HasMany
    {
        return $this->hasMany(ModuleMembership::class);
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class)->withTimestamps();
    }

    public function hasUnrestrictedRhCompanyAccess(): bool
    {
        return $this->isAdministrator();
    }

    public function canAccessCompany(Company $company): bool
    {
        if ($this->hasUnrestrictedRhCompanyAccess()) {
            return true;
        }

        if ($this->user_type !== UserType::Usuario) {
            return false;
        }

        if (! $this->hasModulePermission(AppModule::Rh, ModulePermissionLevel::View)) {
            return false;
        }

        return $this->companies()->whereKey($company->getKey())->exists();
    }

    /** @param  array<int|string>  $companyIds */
    public function syncCompanyAccess(array $companyIds): void
    {
        $this->companies()->sync($companyIds);
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
