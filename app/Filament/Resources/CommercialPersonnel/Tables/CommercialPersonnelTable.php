<?php

namespace App\Filament\Resources\CommercialPersonnel\Tables;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CommercialPersonnelTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Recurso')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('daily_cost_mxn')
                    ->label('Costo / día MXN')
                    ->money('MXN')
                    ->sortable(),
                TextColumn::make('daily_cost_usd')
                    ->label('Costo / día USD')
                    ->money('USD')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->filters([TrashedFilter::make()])
            ->defaultSort('name');
    }
}
