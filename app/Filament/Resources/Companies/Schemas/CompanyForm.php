<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Identificación')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('legal_name')
                            ->label('Razón social')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('trade_name')
                            ->label('Nombre comercial')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('rfc')
                            ->label('RFC')
                            ->maxLength(13)
                            ->columnSpanFull(),
                        TextInput::make('tax_regime')
                            ->label('Régimen fiscal')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label('Activa')
                            ->default(true)
                            ->columnSpanFull(),
                    ]),
                Section::make('Contacto y ubicación')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Textarea::make('address')
                            ->label('Dirección')
                            ->rows(3)
                            ->columnSpanFull(),
                        TextInput::make('contact_phone')
                            ->label('Teléfono')
                            ->tel()
                            ->maxLength(30),
                        TextInput::make('contact_email')
                            ->label('Correo de contacto')
                            ->email()
                            ->maxLength(255),
                    ]),
            ]);
    }
}