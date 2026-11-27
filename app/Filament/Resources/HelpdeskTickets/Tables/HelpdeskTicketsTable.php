<?php

namespace App\Filament\Resources\HelpdeskTickets\Tables;

use App\Enums\HelpdeskTicketSeverity;
use App\Enums\HelpdeskTicketStatus;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class HelpdeskTicketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subject')
                    ->label('Asunto')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('account.name')
                    ->label('Cuenta')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('Categoría')
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Etapa')
                    ->badge()
                    ->sortable(),
                TextColumn::make('severity')
                    ->label('Severidad')
                    ->badge()
                    ->sortable(),
                TextColumn::make('opened_at')
                    ->label('Apertura')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('assignee_name')
                    ->label('Responsable')
                    ->state(fn ($record): string => $record->assignee?->fullName() ?? '—')
                    ->toggleable(),
                TextColumn::make('company.trade_name')
                    ->label('Empresa')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('opened_at', 'desc')
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('status')
                    ->label('Etapa')
                    ->options(HelpdeskTicketStatus::class),
                SelectFilter::make('severity')
                    ->label('Severidad')
                    ->options(HelpdeskTicketSeverity::class),
            ]);
    }
}
