<?php

namespace App\Filament\Resources\CommercialSuppliers\Tables;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CommercialSuppliersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Proveedor')->searchable()->sortable(),
                TextColumn::make('email')->label('Correo')->searchable(),
                IconColumn::make('is_active')->label('Activo')->boolean(),
            ])
            ->filters([TrashedFilter::make()])
            ->defaultSort('name');
    }
}
