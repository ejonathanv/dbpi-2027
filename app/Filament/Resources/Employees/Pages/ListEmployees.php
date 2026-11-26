<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\Employees\Concerns\AuthorizesEmployeeCompany;
use App\Filament\Resources\Employees\EmployeeResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;

class ListEmployees extends ListRecords
{
    use AuthorizesEmployeeCompany;

    protected static string $resource = EmployeeResource::class;

    protected static ?string $breadcrumb = 'Listado';

    #[\Filament\Support\Components\Attributes\Unexposed]
    public function getTitle(): string | Htmlable
    {
        return 'Empleados — '.$this->company->legal_name;
    }

    public function mount(): void
    {
        $this->mountEmployeeCompany($this->resolveCompanyFromRoute());

        parent::mount();
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->whereBelongsTo($this->company);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('volver')
                ->label('Cambiar empresa')
                ->icon(Heroicon::OutlinedArrowLeft)
                ->color('gray')
                ->url(EmployeeResource::getUrl('index')),
            CreateAction::make()
                ->url(EmployeeResource::getUrl('create', ['company' => $this->company])),
        ];
    }
}