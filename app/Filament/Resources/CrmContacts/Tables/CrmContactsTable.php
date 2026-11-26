<?php

namespace App\Filament\Resources\CrmContacts\Tables;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CrmContactsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('account.name')
                    ->label('Cuenta')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('first_name')
                    ->label('Nombre')
                    ->formatStateUsing(fn ($record): string => $record->fullName())
                    ->searchable(['first_name', 'last_name', 'second_last_name']),
                TextColumn::make('email')->label('Correo')->searchable(),
                TextColumn::make('phone')->label('Teléfono'),
                TextColumn::make('job_title')->label('Puesto')->toggleable(),
                IconColumn::make('is_primary')->label('Principal')->boolean(),
            ])
            ->defaultSort('last_name')
            ->filters([
                TrashedFilter::make(),
            ]);
    }
}
