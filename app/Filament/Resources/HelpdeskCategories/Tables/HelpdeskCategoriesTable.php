<?php

namespace App\Filament\Resources\HelpdeskCategories\Tables;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HelpdeskCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fullPath')
                    ->label('Ruta')
                    ->state(fn ($record): string => $record->fullPath())
                    ->searchable(['name']),
                TextColumn::make('depth')
                    ->label('Nivel')
                    ->badge()
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label('Orden')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean(),
            ])
            ->defaultSort('depth');
    }
}
