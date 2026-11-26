<?php

namespace App\Filament\Resources\CrmContacts\Schemas;

use App\Models\CrmAccount;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CrmContactForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('crm_account_id')
                    ->label('Cuenta')
                    ->options(fn (): array => CrmAccount::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->preload()
                    ->required()
                    ->native(false)
                    ->columnSpanFull(),
                TextInput::make('first_name')->label('Nombre(s)')->required()->maxLength(255),
                TextInput::make('last_name')->label('Apellido paterno')->required()->maxLength(255),
                TextInput::make('second_last_name')->label('Apellido materno')->maxLength(255),
                TextInput::make('job_title')->label('Puesto')->maxLength(255),
                TextInput::make('email')->label('Correo')->email()->maxLength(255),
                TextInput::make('phone')->label('Teléfono')->tel()->maxLength(30),
                Toggle::make('is_primary')->label('Contacto principal')->default(false)->columnSpanFull(),
                Textarea::make('notes')->label('Notas')->rows(3)->columnSpanFull(),
            ]);
    }
}
