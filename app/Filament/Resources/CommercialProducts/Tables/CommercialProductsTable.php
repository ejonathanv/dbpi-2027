<?php

namespace App\Filament\Resources\CommercialProducts\Tables;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CommercialProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_paths')
                    ->label('Foto')
                    ->disk('public')
                    ->height(48)
                    ->limit(1),
                TextColumn::make('name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('part_number')
                    ->label('No. parte')
                    ->searchable(),
                TextColumn::make('category.name')
                    ->label('Categoría')
                    ->sortable(),
                TextColumn::make('type.name')
                    ->label('Tipo')
                    ->sortable(),
                TextColumn::make('brand')
                    ->label('Marca')
                    ->toggleable(),
                TextColumn::make('model')
                    ->label('Modelo')
                    ->toggleable(),
                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('commercial_product_category_id')
                    ->label('Categoría')
                    ->relationship('category', 'name'),
                SelectFilter::make('commercial_product_type_id')
                    ->label('Tipo')
                    ->relationship('type', 'name'),
                TrashedFilter::make(),
            ])
            ->defaultSort('name');
    }
}
