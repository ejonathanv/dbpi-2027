<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\Companies\CompanyResource;
use App\Filament\Resources\Employees\EmployeeResource;
use App\Models\Company;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
class BrowseCompanies extends ListRecords
{
    protected static string $resource = EmployeeResource::class;

    protected static ?string $title = 'Empleados por empresa';

    protected static ?string $breadcrumb = 'Empresas';

    #[\Filament\Support\Components\Attributes\Unexposed]
    public function getTitle(): string | Htmlable
    {
        return static::$title ?? 'Empleados por empresa';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): \Illuminate\Database\Eloquent\Builder => CompanyResource::getEloquentQuery()->withCount('employees'))
            ->columns([
                TextColumn::make('legal_name')
                    ->label('Razón social')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('trade_name')
                    ->label('Nombre comercial')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employees_count')
                    ->label('Empleados')
                    ->sortable(),
            ])
            ->defaultSort('legal_name')
            ->recordActions([
                Action::make('ver_empleados')
                    ->label('Ver empleados')
                    ->icon(Heroicon::OutlinedUsers)
                    ->url(fn (Company $record): string => EmployeeResource::getUrl('list', ['company' => $record])),
            ])
            ->recordUrl(fn (Company $record): string => EmployeeResource::getUrl('list', ['company' => $record]));
    }
}