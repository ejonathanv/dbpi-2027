<?php

namespace App\Support;

use App\Models\CommercialFupContractItem;
use App\Models\CrmAccount;
use App\Models\HelpdeskContract;
use Illuminate\Support\Carbon;

class CommercialFupContractSync
{
    public static function syncToHelpdesk(CommercialFupContractItem $item): void
    {
        $item->loadMissing(['fup.quote', 'contract']);

        $accountId = $item->fup?->quote?->crm_account_id;
        $contract = $item->contract;
        $startsAt = $item->starts_at;

        if (! $accountId || ! $contract instanceof HelpdeskContract || ! $startsAt) {
            return;
        }

        $startsAt = Carbon::parse($startsAt)->startOfDay();
        $endsAt = $contract->computeEndsAt($startsAt);

        $item->update(['ends_at' => $endsAt->toDateString()]);

        $account = CrmAccount::query()->find($accountId);

        if (! $account) {
            return;
        }

        $pivot = [
            'starts_at' => $startsAt->toDateString(),
            'ends_at' => $endsAt->toDateString(),
        ];

        if ($account->helpdeskContracts()->where('helpdesk_contracts.id', $contract->getKey())->exists()) {
            $account->helpdeskContracts()->updateExistingPivot($contract->getKey(), $pivot);
        } else {
            $account->helpdeskContracts()->attach($contract->getKey(), $pivot);
        }
    }
}