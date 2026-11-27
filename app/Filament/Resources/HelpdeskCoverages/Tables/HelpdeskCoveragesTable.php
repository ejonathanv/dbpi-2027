<?php

namespace App\Filament\Resources\HelpdeskCoverages\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class HelpdeskCoveragesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Cobertura')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('accounts_count')
                    ->label('Cuentas')
                    ->counts('accounts')
                    ->sortable(),
                TextColumn::make('weekdaysLabel')
                    ->label('Días')
                    ->state(fn ($record): string => $record->weekdaysLabel()),
                TextColumn::make('scheduleLabel')
                    ->label('Horario')
                    ->state(fn ($record): string => $record->scheduleLabel()),
            ])
            ->filters([
                SelectFilter::make('accounts')
                    ->label('Cuenta')
                    ->relationship('accounts', 'name')
                    ->searchable()
                    ->preload(),
                TrashedFilter::make(),
            ])
            ->defaultSort('name');
    }
}
