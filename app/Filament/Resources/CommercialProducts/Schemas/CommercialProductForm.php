<?php

namespace App\Filament\Resources\CommercialProducts\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CommercialProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Producto')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->label('Nombre')->required()->maxLength(255)->columnSpanFull(),
                        TextInput::make('sku')->label('SKU')->maxLength(100),
                        Toggle::make('is_active')->label('Activo')->default(true),
                    ]),
            ]);
    }
}
