<?php

namespace App\Filament\Resources\CommercialFups\Pages;

use App\Filament\Resources\CommercialFups\CommercialFupResource;
use App\Models\CommercialFup;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateCommercialFup extends CreateRecord
{
    protected static string $resource = CommercialFupResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['fup_number'] = CommercialFup::generateFupNumber();
        $data['fup_date'] = $data['fup_date'] ?? now()->toDateString();
        $data['created_by_user_id'] = Filament::auth()->id();

        return $data;
    }
}
