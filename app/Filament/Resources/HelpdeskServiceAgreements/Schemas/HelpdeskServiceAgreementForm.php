<?php

namespace App\Filament\Resources\HelpdeskServiceAgreements\Schemas;

use App\Enums\HelpdeskSlaResponseUnit;
use App\Enums\HelpdeskTicketSeverity;
use App\Models\CrmAccount;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class HelpdeskServiceAgreementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Acuerdo de servicio')
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
                            ->label('Nombre del acuerdo')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('Descripción general')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
                Section::make('Tiempos de respuesta por severidad')
                    ->schema([
                        Repeater::make('severityResponses')
                            ->relationship()
                            ->label('')
                            ->schema([
                                Select::make('severity')
                                    ->label('Severidad')
                                    ->options(HelpdeskTicketSeverity::class)
                                    ->required()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->native(false),
                                TextInput::make('response_number')
                                    ->label('Número')
                                    ->numeric()
                                    ->integer()
                                    ->required()
                                    ->minValue(1),
                                Select::make('response_unit')
                                    ->label('Tiempo')
                                    ->options(HelpdeskSlaResponseUnit::class)
                                    ->required()
                                    ->native(false),
                            ])
                            ->columns(3)
                            ->addActionLabel('Agregar severidad')
                            ->defaultItems(0)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
