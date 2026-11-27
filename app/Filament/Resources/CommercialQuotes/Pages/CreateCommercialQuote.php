<?php

namespace App\Filament\Resources\CommercialQuotes\Pages;

use App\Enums\SalaryCurrency;
use App\Filament\Resources\CommercialQuotes\CommercialQuoteResource;
use App\Models\CommercialQuote;
use App\Models\SystemSetting;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Carbon;

class CreateCommercialQuote extends CreateRecord
{
    protected static string $resource = CommercialQuoteResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        $data['quote_number'] = CommercialQuote::generateQuoteNumber();
        $data['quoted_at'] ??= Carbon::today()->toDateString();
        $data['presentation_currency'] ??= SalaryCurrency::Mxn->value;
        $data['exchange_rate'] ??= SystemSetting::getDecimal('exchange_rate_usd_mxn', 18);
        $data['validity_days'] ??= 30;
        $data['created_by_user_id'] = $user instanceof User ? $user->getKey() : null;

        return $data;
    }
}
