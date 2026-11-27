<?php

namespace App\Support;

use App\Enums\CommercialQuoteAuthorizationStatus;
use App\Enums\CommercialQuoteLineItemType;
use App\Models\CommercialFup;
use App\Models\CommercialFupContractItem;
use App\Models\CommercialQuote;
use App\Models\CommercialQuoteAuthorizer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CommercialFupGenerationService
{
    /**
     * @param  list<int>  $reviewerUserIds
     */
    public static function createFromQuote(CommercialQuote $quote, User $requester, array $reviewerUserIds): CommercialFup
    {
        if (! $quote->canGenerateFup()) {
            throw ValidationException::withMessages([
                'fup' => 'La cotización no puede generar FUP en su estado actual.',
            ]);
        }

        $reviewerUserIds = array_values(array_unique(array_map('intval', $reviewerUserIds)));

        if ($reviewerUserIds === []) {
            throw ValidationException::withMessages([
                'reviewer_ids' => 'Seleccione al menos un autorizador.',
            ]);
        }

        $activeAuthorizerIds = CommercialQuoteAuthorizer::query()
            ->where('is_active', true)
            ->whereIn('user_id', $reviewerUserIds)
            ->pluck('user_id')
            ->all();

        if (count($activeAuthorizerIds) !== count($reviewerUserIds)) {
            throw ValidationException::withMessages([
                'reviewer_ids' => 'Uno o más autorizadores no están activos en el catálogo.',
            ]);
        }

        return DB::transaction(function () use ($quote, $requester, $reviewerUserIds): CommercialFup {
            $fup = CommercialFup::query()->create([
                'fup_number' => CommercialFup::generateFupNumber(),
                'commercial_quote_id' => $quote->getKey(),
                'fup_date' => now()->toDateString(),
                'authorization_status' => CommercialQuoteAuthorizationStatus::Draft,
                'created_by_user_id' => $requester->getKey(),
            ]);

            $quote->loadMissing(['groups.lineItems']);

            foreach ($quote->groups as $group) {
                foreach ($group->lineItems as $item) {
                    if ($item->type !== CommercialQuoteLineItemType::Contract) {
                        continue;
                    }

                    if (blank($item->helpdesk_contract_id)) {
                        continue;
                    }

                    CommercialFupContractItem::query()->create([
                        'commercial_fup_id' => $fup->getKey(),
                        'commercial_quote_line_item_id' => $item->getKey(),
                        'helpdesk_contract_id' => $item->helpdesk_contract_id,
                    ]);
                }
            }

            CommercialFupAuthorizationService::request($fup, $requester, $reviewerUserIds);

            return $fup->refresh();
        });
    }
}