<?php

namespace App\Filament\Resources\CrmCalendarEvents\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CrmCalendarEventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('starts_at')
                    ->label('Inicio')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('account.name')
                    ->label('Cuenta')
                    ->toggleable(),
                TextColumn::make('contact.first_name')
                    ->label('Contacto')
                    ->formatStateUsing(fn ($record): string => $record->contact?->fullName() ?? '—')
                    ->toggleable(),
                TextColumn::make('opportunity.name')
                    ->label('Oportunidad')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('starts_at', 'desc');
    }
}
