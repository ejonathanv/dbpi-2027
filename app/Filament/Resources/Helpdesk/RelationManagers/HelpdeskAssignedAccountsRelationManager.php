<?php

namespace App\Filament\Resources\Helpdesk\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class HelpdeskAssignedAccountsRelationManager extends RelationManager
{
    protected static string $relationship = 'accounts';

    protected static ?string $title = 'Cuentas asignadas';

    protected static ?string $modelLabel = 'cuenta';

    protected static ?string $pluralModelLabel = 'Cuentas';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Cuenta')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('legal_name')
                    ->label('Razón social')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('primary_email')
                    ->label('Correo')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->headerActions([
                AttachAction::make()
                    ->label('Asignar cuentas')
                    ->modalHeading('Asignar cuentas CRM')
                    ->modalSubmitActionLabel('Asignar')
                    ->preloadRecordSelect()
                    ->multiple()
                    ->recordSelectOptionsQuery(fn (Builder $query): Builder => $query->orderBy('name')),
            ])
            ->recordActions([
                DetachAction::make()
                    ->label('Quitar'),
            ])
            ->toolbarActions([
                DetachBulkAction::make()
                    ->label('Quitar seleccionadas'),
            ]);
    }
}
