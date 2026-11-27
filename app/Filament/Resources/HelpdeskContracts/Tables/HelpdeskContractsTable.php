<?php

namespace App\Filament\Resources\HelpdeskContracts\Tables;

use Filament\Tables\Columns\TextColumn;
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
                TextColumn::make('account.name')
                    ->label('Cuenta')
                    ->searchable()
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
                TrashedFilter::make(),
            ])
            ->defaultSort('name');
    }
}
