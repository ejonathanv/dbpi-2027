<?php

namespace App\Filament\Resources\HelpdeskContracts\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class HelpdeskContractsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Contrato')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('accounts_count')
                    ->label('Cuentas')
                    ->counts('accounts')
                    ->sortable(),
                TextColumn::make('validityLabel')
                    ->label('Vigencia')
                    ->state(fn ($record): string => $record->validityLabel()),
                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
