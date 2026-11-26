<?php

namespace App\Filament\Resources\CrmCalendarEvents\Schemas;

use App\Models\CrmAccount;
use App\Models\CrmContact;
use App\Models\CrmOpportunity;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class CrmCalendarEventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Evento')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        DateTimePicker::make('starts_at')
                            ->label('Inicio')
                            ->required()
                            ->native(false),
                        DateTimePicker::make('ends_at')
                            ->label('Fin')
                            ->native(false),
                        Toggle::make('all_day')
                            ->label('Todo el día')
                            ->default(false)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                Section::make('Relacionado con')
                    ->columns(2)
                    ->schema([
                        Select::make('crm_account_id')
                            ->label('Cuenta')
                            ->options(fn (): array => CrmAccount::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('crm_contact_id', null))
                            ->native(false),
                        Select::make('crm_contact_id')
                            ->label('Contacto')
                            ->options(function (Get $get): array {
                                $accountId = $get('crm_account_id');
                                if (blank($accountId)) {
                                    return [];
                                }

                                return CrmContact::query()
                                    ->where('crm_account_id', $accountId)
                                    ->orderBy('first_name')
                                    ->get()
                                    ->mapWithKeys(fn (CrmContact $c): array => [$c->getKey() => $c->fullName()])
                                    ->all();
                            })
                            ->searchable()
                            ->native(false),
                        Select::make('crm_opportunity_id')
                            ->label('Oportunidad')
                            ->options(function (Get $get): array {
                                $accountId = $get('crm_account_id');
                                if (blank($accountId)) {
                                    return [];
                                }

                                return CrmOpportunity::query()
                                    ->where('crm_account_id', $accountId)
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->all();
                            })
                            ->searchable()
                            ->native(false)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
