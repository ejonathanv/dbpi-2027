<?php

namespace App\Filament\Resources\CrmAccounts\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ContactsRelationManager extends RelationManager
{
    protected static string $relationship = 'contacts';

    protected static ?string $title = 'Contactos';

    protected static ?string $modelLabel = 'contacto';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('first_name')->label('Nombre(s)')->required()->maxLength(255),
                TextInput::make('last_name')->label('Apellido paterno')->required()->maxLength(255),
                TextInput::make('second_last_name')->label('Apellido materno')->maxLength(255),
                TextInput::make('job_title')->label('Puesto')->maxLength(255),
                TextInput::make('email')->label('Correo')->email()->maxLength(255),
                TextInput::make('phone')->label('Teléfono')->tel()->maxLength(30),
                Toggle::make('is_primary')->label('Contacto principal')->default(false),
                Textarea::make('notes')->label('Notas')->rows(2)->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('first_name')
                    ->label('Nombre')
                    ->formatStateUsing(fn ($record): string => $record->fullName())
                    ->searchable(['first_name', 'last_name', 'second_last_name']),
                TextColumn::make('email')->label('Correo'),
                TextColumn::make('phone')->label('Teléfono'),
                TextColumn::make('job_title')->label('Puesto')->toggleable(),
                IconColumn::make('is_primary')->label('Principal')->boolean(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
