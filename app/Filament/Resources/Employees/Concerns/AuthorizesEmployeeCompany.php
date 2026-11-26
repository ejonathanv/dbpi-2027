<?php

namespace App\Filament\Resources\Employees\Concerns;

use App\Filament\Resources\Companies\CompanyResource;
use App\Models\Company;
use App\Models\User;
use Livewire\Attributes\Locked;

trait AuthorizesEmployeeCompany
{
    #[Locked]
    public Company $company;

    protected function resolveCompanyFromRoute(): Company
    {
        $company = request()->route('company');

        if ($company instanceof Company) {
            return $company;
        }

        return CompanyResource::getEloquentQuery()
            ->whereKey($company)
            ->firstOrFail();
    }

    protected function mountEmployeeCompany(Company $company): void
    {
        $this->company = $company;

        $user = auth()->user();

        abort_unless($user instanceof User && $user->canAccessCompany($company), 403);
    }
}