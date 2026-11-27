<?php

namespace App\Filament\Resources\CommercialProducts\Tables;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CommercialProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Producto')->searchable()->sortable(),
                TextColumn::make('sku')->label('SKU')->searchable(),
                IconColumn::make('is_active')->label('Activo')->boolean(),
            ])
            ->filters([TrashedFilter::make()])
            ->defaultSort('name');
    }
}
