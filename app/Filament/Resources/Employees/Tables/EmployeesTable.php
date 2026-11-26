<?php

namespace App\Filament\Resources\Employees\Tables;

use App\Filament\Resources\Employees\EmployeeResource;
use App\Models\Employee;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class EmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->label('Nombre')
                    ->getStateUsing(fn ($record): string => $record->fullName())
                    ->searchable(['first_name', 'last_name', 'second_last_name'])
                    ->sortable(['last_name', 'first_name']),
                TextColumn::make('employee_number')
                    ->label('No. empleado')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Estatus')
                    ->badge()
                    ->sortable(),
                TextColumn::make('hire_date')
                    ->label('Ingreso')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('email')
                    ->label('Correo')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('last_name')
            ->filters([
                TrashedFilter::make()
                    ->label('Registros eliminados'),
                SelectFilter::make('status')
                    ->label('Estatus')
                    ->options(\App\Enums\EmployeeStatus::class),
            ])
            ->recordActions([
                EditAction::make()
                    ->url(fn (Employee $record): string => EmployeeResource::getUrl('edit', [
                        'company' => $record->company_id,
                        'record' => $record,
                    ])),
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