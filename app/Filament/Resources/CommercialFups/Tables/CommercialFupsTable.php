<?php

namespace App\Filament\Resources\CommercialFups\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CommercialFupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fup_number')->label('FUP')->searchable()->sortable(),
                TextColumn::make('authorization_status')->label('Autorización')->badge(),
                TextColumn::make('quote.quote_number')->label('Cotización'),
            ])
            ->filters([TrashedFilter::make()])
            ->defaultSort('fup_number', 'desc');
    }
}
