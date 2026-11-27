<?php

namespace App\Filament\Resources\HelpdeskTickets\Schemas;

use App\Enums\HelpdeskTicketSeverity;
use App\Enums\HelpdeskTicketStatus;
use App\Filament\Resources\HelpdeskTickets\Pages\EditHelpdeskTicket;
use App\Filament\Resources\HelpdeskTickets\RelationManagers\TicketEventsRelationManager;
use App\Models\Company;
use App\Models\CompanyDepartment;
use App\Models\CrmAccount;
use App\Models\Employee;
use App\Models\HelpdeskCategory;
use App\Models\HelpdeskTicket;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class HelpdeskTicketForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Tabs::make('Ticket')
                    ->tabs([
                        Tab::make('Solicitud')
                            ->schema([
                                Section::make('Datos del ticket')
                                    ->columns(2)
                                    ->schema(self::requestFields()),
                                Section::make('Abierto por')
                                    ->visible(fn (string $operation): bool => $operation === 'edit')
                                    ->schema([
                                        Placeholder::make('opened_by_display')
                                            ->label('Solicitante')
                                            ->content(fn (?HelpdeskTicket $record): string => $record?->openedByLabel() ?? '—'),
                                    ]),
                            ]),
                        Tab::make('Asignación interna')
                            ->schema([
                                Section::make()
                                    ->columns(2)
                                    ->schema(self::assignmentFields()),
                            ]),
                        Tab::make('Eventos')
                            ->visible(fn (string $operation): bool => $operation === 'edit')
                            ->schema([
                                Livewire::make(TicketEventsRelationManager::class, fn (Livewire $component): array => [
                                    'ownerRecord' => $component->getRecord(),
                                    'pageClass' => EditHelpdeskTicket::class,
                                ])->lazyLoaded(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    /** @return array<int, mixed> */
    protected static function requestFields(): array
    {
        return [
            Select::make('crm_account_id')
                ->label('Cuenta (CRM)')
                ->options(fn (): array => CrmAccount::query()->orderBy('name')->pluck('name', 'id')->all())
                ->searchable()
                ->preload()
                ->required()
                ->native(false)
                ->columnSpanFull(),
            TextInput::make('subject')
                ->label('Asunto')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),
            Textarea::make('description')
                ->label('Descripción')
                ->rows(4)
                ->columnSpanFull(),
            DateTimePicker::make('opened_at')
                ->label('Fecha de apertura')
                ->default(now())
                ->required()
                ->native(false),
            Select::make('helpdesk_category_id')
                ->label('Categoría')
                ->options(fn (): array => HelpdeskCategory::optionsForSelect())
                ->searchable()
                ->required()
                ->native(false),
            Select::make('status')
                ->label('Etapa')
                ->options(HelpdeskTicketStatus::class)
                ->required()
                ->visible(fn (string $operation): bool => $operation === 'edit')
                ->native(false),
            Select::make('severity')
                ->label('Severidad')
                ->options(HelpdeskTicketSeverity::class)
                ->default(HelpdeskTicketSeverity::Normal)
                ->required()
                ->native(false),
        ];
    }

    /** @return array<int, mixed> */
    protected static function assignmentFields(): array
    {
        return [
            Select::make('company_id')
                ->label('Empresa')
                ->options(fn (): array => Company::query()->orderBy('legal_name')->pluck('legal_name', 'id')->all())
                ->searchable()
                ->preload()
                ->required()
                ->live()
                ->afterStateUpdated(function (Set $set): void {
                    $set('company_department_id', null);
                    $set('employee_id', null);
                })
                ->native(false),
            Select::make('company_department_id')
                ->label('Departamento')
                ->options(function (Get $get): array {
                    $companyId = $get('company_id');

                    if (blank($companyId)) {
                        return [];
                    }

                    return CompanyDepartment::query()
                        ->where('company_id', $companyId)
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all();
                })
                ->searchable()
                ->required()
                ->live()
                ->afterStateUpdated(fn (Set $set) => $set('employee_id', null))
                ->native(false),
            Select::make('employee_id')
                ->label('Responsable')
                ->options(function (Get $get): array {
                    $departmentId = $get('company_department_id');
                    $companyId = $get('company_id');

                    if (blank($departmentId) || blank($companyId)) {
                        return [];
                    }

                    return Employee::query()
                        ->where('company_id', $companyId)
                        ->where('company_department_id', $departmentId)
                        ->orderBy('first_name')
                        ->get()
                        ->mapWithKeys(fn (Employee $employee): array => [
                            $employee->getKey() => $employee->fullName(),
                        ])
                        ->all();
                })
                ->searchable()
                ->native(false)
                ->columnSpanFull(),
        ];
    }
}
