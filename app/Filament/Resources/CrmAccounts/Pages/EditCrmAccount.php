<?php

namespace App\Filament\Resources\CrmAccounts\Pages;

use App\Filament\Resources\CrmAccounts\CrmAccountResource;
use App\Models\CrmAccount;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditCrmAccount extends EditRecord
{
    protected static string $resource = CrmAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (array_key_exists('logo_path', $data)) {
            $path = CrmAccount::normalizeLogoPath($data['logo_path']);

            if (filled($path) && ! Storage::disk('public')->exists($path)) {
                $path = null;
            }

            $data['logo_path'] = $path;
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (array_key_exists('logo_path', $data)) {
            $data['logo_path'] = CrmAccount::normalizeLogoPath($data['logo_path']);
        }

        return $data;
    }
}
