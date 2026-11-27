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
                TextColumn::make('name')->label('Nombre')->searchable()->sortable(),
                TextColumn::make('role')->label('Rol')->searchable(),
                IconColumn::make('is_active')->label('Activo')->boolean(),
            ])
            ->filters([TrashedFilter::make()])
            ->defaultSort('name');
    }
}
