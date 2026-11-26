<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\Employees\Concerns\AuthorizesEmployeeCompany;
use App\Filament\Resources\Employees\EmployeeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployee extends CreateRecord
{
    use AuthorizesEmployeeCompany;

    protected static string $resource = EmployeeResource::class;

    public function mount(): void
    {
        $this->mountEmployeeCompany($this->resolveCompanyFromRoute());

        parent::mount();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['company_id'] = $this->company->getKey();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return EmployeeResource::getUrl('list', ['company' => $this->company]);
    }
}