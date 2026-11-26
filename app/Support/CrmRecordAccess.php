<?php

namespace App\Support;

use App\Enums\AppModule;
use App\Enums\CrmRecordScope;
use App\Enums\ModulePermissionLevel;
use App\Enums\UserType;
use App\Models\CrmCalendarEvent;
use App\Models\CrmOpportunity;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class CrmRecordAccess
{
    public static function appliesOwnRecordsScope(?User $user = null): bool
    {
        $user ??= auth()->user();

        if (! $user instanceof User) {
            return false;
        }

        if ($user->isAdministrator() || $user->user_type !== UserType::Usuario) {
            return false;
        }

        if (! $user->hasModulePermission(AppModule::Crm, ModulePermissionLevel::View)) {
            return false;
        }

        return $user->crm_record_scope === CrmRecordScope::Own;
    }

    /**
     * @param  Builder<CrmOpportunity>  $query
     * @return Builder<CrmOpportunity>
     */
    public static function scopeOpportunitiesQuery(Builder $query, ?User $user = null): Builder
    {
        if (! self::appliesOwnRecordsScope($user)) {
            return $query;
        }

        $user ??= auth()->user();

        return $query->where('assigned_user_id', $user->getKey());
    }

    public static function userCanAccessOpportunity(CrmOpportunity $opportunity, ?User $user = null): bool
    {
        if (! self::appliesOwnRecordsScope($user)) {
            return true;
        }

        $user ??= auth()->user();

        return (int) $opportunity->assigned_user_id === (int) $user->getKey();
    }

    /**
     * @param  Builder<CrmCalendarEvent>  $query
     * @return Builder<CrmCalendarEvent>
     */
    public static function scopeCalendarEventsQuery(Builder $query, ?User $user = null): Builder
    {
        if (! self::appliesOwnRecordsScope($user)) {
            return $query;
        }

        $user ??= auth()->user();
        $userId = $user->getKey();

        return $query->where(function (Builder $builder) use ($userId): void {
            $builder
                ->where('created_by', $userId)
                ->orWhereHas('opportunity', fn (Builder $opportunityQuery): Builder => $opportunityQuery->where('assigned_user_id', $userId));
        });
    }

    public static function userCanAccessCalendarEvent(CrmCalendarEvent $event, ?User $user = null): bool
    {
        if (! self::appliesOwnRecordsScope($user)) {
            return true;
        }

        $user ??= auth()->user();
        $userId = (int) $user->getKey();

        if ((int) $event->created_by === $userId) {
            return true;
        }

        $opportunity = $event->relationLoaded('opportunity')
            ? $event->opportunity
            : $event->opportunity()->first();

        return $opportunity !== null && (int) $opportunity->assigned_user_id === $userId;
    }
}
