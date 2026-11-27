<?php

namespace App\Filament\Resources\CrmAccounts\Pages;

use App\Filament\Resources\CrmAccounts\CrmAccountResource;
use App\Models\CrmAccount;
use Filament\Resources\Pages\CreateRecord;

class CreateCrmAccount extends CreateRecord
{
    protected static string $resource = CrmAccountResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (array_key_exists('logo_path', $data)) {
            $data['logo_path'] = CrmAccount::normalizeLogoPath($data['logo_path']);
        }

        return $data;
    }
}
