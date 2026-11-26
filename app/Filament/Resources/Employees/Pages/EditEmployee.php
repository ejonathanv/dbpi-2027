<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\Employees\Concerns\AuthorizesEmployeeCompany;
use App\Filament\Resources\Employees\EmployeeResource;
use App\Filament\Resources\Employees\Schemas\EmployeeEditForm;
use Filament\Actions\DeleteAction;
use Filament\Schemas\Schema;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditEmployee extends EditRecord
{
    use AuthorizesEmployeeCompany;

    protected static string $resource = EmployeeResource::class;

    public function form(Schema $schema): Schema
    {
        return EmployeeEditForm::configure($schema);
    }

    public function mount(int | string $record): void
    {
        $this->mountEmployeeCompany($this->resolveCompanyFromRoute());

        parent::mount($record);

        abort_unless((int) $this->record->company_id === (int) $this->company->getKey(), 404);

        $this->record->load(['photos', 'attachments']);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function getRedirectUrl(): ?string
    {
        return null;
    }

    protected function afterSave(): void
    {
        $this->record->refresh();
        $this->record->load(['photos', 'attachments']);
    }
}