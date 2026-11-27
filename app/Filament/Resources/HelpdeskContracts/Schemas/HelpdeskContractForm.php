<?php

namespace App\Filament\Resources\HelpdeskContracts\Schemas;

use App\Enums\HelpdeskContractValidityUnit;
use App\Models\CrmAccount;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class HelpdeskContractForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Contrato')
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
                            ->label('Nombre del contrato')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('Descripción general')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
                Section::make('Vigencia')
                    ->columns(2)
                    ->schema([
                        TextInput::make('validity_number')
                            ->label('Número')
                            ->numeric()
                            ->integer()
                            ->required()
                            ->minValue(1),
                        Select::make('validity_unit')
                            ->label('Tiempo')
                            ->options(HelpdeskContractValidityUnit::class)
                            ->required()
                            ->native(false),
                    ]),
            ]);
    }
}
