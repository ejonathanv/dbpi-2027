<?php

namespace App\Filament\Resources\CommercialQuotes\Tables;

use App\Enums\SalaryCurrency;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CommercialQuotesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('quote_number')
                    ->label('Folio')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('account.name')
                    ->label('Cuenta')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quoted_at')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),
                TextColumn::make('presentation_currency')
                    ->label('Moneda')
                    ->badge(),
                TextColumn::make('opportunity.name')
                    ->label('Oportunidad')
                    ->toggleable()
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('presentation_currency')
                    ->label('Moneda')
                    ->options(SalaryCurrency::class),
                TrashedFilter::make(),
            ])
            ->defaultSort('quoted_at', 'desc');
    }
}
