<?php

namespace App\Filament\Resources\Companies\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CompaniesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('legal_name')
                    ->label('Razón social')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('trade_name')
                    ->label('Nombre comercial')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('rfc')
                    ->label('RFC')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('tax_regime')
                    ->label('Régimen fiscal')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('contact_email')
                    ->label('Correo')
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean(),
            ])
            ->defaultSort('legal_name')
            ->filters([
                TrashedFilter::make()
                    ->label('Registros eliminados'),
                SelectFilter::make('is_active')
                    ->label('Estatus')
                    ->options([
                        '1' => 'Activas',
                        '0' => 'Inactivas',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}