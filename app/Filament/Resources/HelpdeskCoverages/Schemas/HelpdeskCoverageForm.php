<?php

namespace App\Filament\Resources\HelpdeskCoverages\Schemas;

use App\Models\CrmAccount;
use App\Support\WeekdayOptions;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class HelpdeskCoverageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Cobertura')
                    ->columns(2)
                    ->schema([
                        Select::make('crm_account_id')
                            ->label('Cuenta (CRM)')
                            ->options(fn (): array => CrmAccount::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->columnSpanFull(),
                        TextInput::make('name')
                            ->label('Nombre de la cobertura')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),
                Section::make('Días de cobertura')
                    ->schema([
                        Toggle::make('full_week')
                            ->label('Toda la semana')
                            ->default(true)
                            ->live(),
                        CheckboxList::make('weekdays')
                            ->label('Días específicos')
                            ->options(WeekdayOptions::labels())
                            ->columns(2)
                            ->visible(fn (Get $get): bool => ! $get('full_week'))
                            ->required(fn (Get $get): bool => ! $get('full_week'))
                            ->dehydrated(fn (Get $get): bool => ! $get('full_week')),
                    ]),
                Section::make('Horario de cobertura')
                    ->columns(2)
                    ->schema([
                        Toggle::make('all_day')
                            ->label('Todo el día')
                            ->default(false)
                            ->live()
                            ->columnSpanFull(),
                        TimePicker::make('coverage_start')
                            ->label('Desde')
                            ->seconds(false)
                            ->visible(fn (Get $get): bool => ! $get('all_day'))
                            ->required(fn (Get $get): bool => ! $get('all_day')),
                        TimePicker::make('coverage_end')
                            ->label('Hasta')
                            ->seconds(false)
                            ->visible(fn (Get $get): bool => ! $get('all_day'))
                            ->required(fn (Get $get): bool => ! $get('all_day'))
                            ->after('coverage_start'),
                    ]),
            ]);
    }
}
