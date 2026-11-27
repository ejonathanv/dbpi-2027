<?php

namespace App\Filament\Resources\HelpdeskServiceAgreements\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class HelpdeskServiceAgreementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Acuerdo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('account.name')
                    ->label('Cuenta')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('severityResponses_count')
                    ->label('Severidades')
                    ->counts('severityResponses')
                    ->sortable(),
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
