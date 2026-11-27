<?php

namespace App\Support;

use App\Enums\AppModule;
use App\Enums\HelpdeskTicketScope;
use App\Enums\ModulePermissionLevel;
use App\Enums\UserType;
use App\Models\HelpdeskTicket;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class HelpdeskRecordAccess
{
    public static function appliesRestrictedTicketScope(?User $user = null): bool
    {
        $user ??= auth()->user();

        if (! $user instanceof User) {
            return false;
        }

        if ($user->isAdministrator() || $user->user_type !== UserType::Usuario) {
            return false;
        }

        if (! $user->hasModulePermission(AppModule::Helpdesk, ModulePermissionLevel::View)) {
            return false;
        }

        return $user->helpdesk_ticket_scope === HelpdeskTicketScope::AssignedOrOpened;
    }

    /**
     * @param  Builder<HelpdeskTicket>  $query
     * @return Builder<HelpdeskTicket>
     */
    public static function scopeTicketsQuery(Builder $query, ?User $user = null): Builder
    {
        if (! self::appliesRestrictedTicketScope($user)) {
            return $query;
        }

        $user ??= auth()->user();
        $userId = $user->getKey();
        $employeeId = $user->helpdesk_employee_id;

        return $query->where(function (Builder $builder) use ($userId, $employeeId): void {
            $builder->where('opened_by_user_id', $userId);

            if (filled($employeeId)) {
                $builder->orWhere('employee_id', $employeeId);
            }
        });
    }

    public static function userCanAccessTicket(HelpdeskTicket $ticket, ?User $user = null): bool
    {
        if (! self::appliesRestrictedTicketScope($user)) {
            return true;
        }

        $user ??= auth()->user();
        $userId = (int) $user->getKey();

        if ((int) $ticket->opened_by_user_id === $userId) {
            return true;
        }

        if (filled($user->helpdesk_employee_id) && (int) $ticket->employee_id === (int) $user->helpdesk_employee_id) {
            return true;
        }

        return false;
    }
}
