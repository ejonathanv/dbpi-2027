<?php

namespace App\Filament\Resources\CrmContacts\Pages;

use App\Filament\Resources\CrmContacts\CrmContactResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCrmContact extends CreateRecord
{
    protected static string $resource = CrmContactResource::class;

    public function mount(): void
    {
        parent::mount();

        $accountId = request()->integer('crm_account_id');

        if ($accountId > 0) {
            $this->form->fill([
                'crm_account_id' => $accountId,
            ]);
        }
    }
}
