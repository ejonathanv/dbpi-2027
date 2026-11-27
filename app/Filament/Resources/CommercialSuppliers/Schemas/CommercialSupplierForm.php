<?php

namespace App\Filament\Resources\CommercialSuppliers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CommercialSupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Proveedor')->columns(2)->schema([
                TextInput::make('name')->label('Nombre')->required()->maxLength(255)->columnSpanFull(),
                TextInput::make('email')->label('Correo')->email()->maxLength(255),
                Toggle::make('is_active')->label('Activo')->default(true),
            ]),
        ]);
    }
}
