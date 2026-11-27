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
            Section::make('Recurso / personal')->columns(2)->schema([
                TextInput::make('name')->label('Nombre')->required()->maxLength(255),
                TextInput::make('role')->label('Rol')->maxLength(255)->placeholder('Ej. Técnico A'),
                Toggle::make('is_active')->label('Activo')->default(true)->columnSpanFull(),
            ]),
        ]);
    }
}
