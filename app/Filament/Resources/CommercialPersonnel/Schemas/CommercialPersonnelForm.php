<?php

namespace App\Filament\Resources\CommercialPersonnel\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CommercialPersonnelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Recurso / personal')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Nombre del recurso')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    TextInput::make('daily_cost_mxn')
                        ->label('Costo por día (MXN)')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->prefix('$')
                        ->default(0),
                    TextInput::make('daily_cost_usd')
                        ->label('Costo por día (USD)')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->prefix('USD')
                        ->default(0),
                    Toggle::make('is_active')
                        ->label('Activo')
                        ->default(true)
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
