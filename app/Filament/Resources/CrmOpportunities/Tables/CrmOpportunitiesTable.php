<?php

namespace App\Filament\Resources\CrmOpportunities\Tables;

use App\Enums\CrmOpportunitySource;
use App\Enums\CrmOpportunityStage;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CrmOpportunitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Oportunidad')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('account.name')
                    ->label('Cuenta')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('stage')
                    ->label('Etapa')
                    ->badge()
                    ->color(fn (CrmOpportunityStage $state): string => $state->getColor())
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Cantidad')
                    ->money(fn ($record): string => $record->currency ?? 'MXN')
                    ->sortable(),
                TextColumn::make('probability_percent')
                    ->label('Probabilidad')
                    ->suffix('%')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('expected_close_date')
                    ->label('Fecha de cierre')
                    ->date()
                    ->sortable(),
                TextColumn::make('source')
                    ->label('Origen')
                    ->toggleable(),
                TextColumn::make('fob')
                    ->label('FOB')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('assignee.name')
                    ->label('Responsable')
                    ->toggleable(),
            ])
            ->defaultSort('expected_close_date')
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('stage')
                    ->label('Etapa')
                    ->options(CrmOpportunityStage::class),
                SelectFilter::make('source')
                    ->label('Origen')
                    ->options(CrmOpportunitySource::class),
            ]);
    }
}
