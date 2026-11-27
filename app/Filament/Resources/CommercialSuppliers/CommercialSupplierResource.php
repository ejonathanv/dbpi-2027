<?php

namespace App\Filament\Resources\CommercialSuppliers;

use App\Enums\PanelNavigationGroup;
use App\Filament\Resources\CommercialSuppliers\Pages\CreateCommercialSupplier;
use App\Filament\Resources\CommercialSuppliers\Pages\EditCommercialSupplier;
use App\Filament\Resources\CommercialSuppliers\Pages\ListCommercialSuppliers;
use App\Filament\Resources\CommercialSuppliers\Schemas\CommercialSupplierForm;
use App\Filament\Resources\CommercialSuppliers\Tables\CommercialSuppliersTable;
use App\Models\CommercialSupplier;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class CommercialSupplierResource extends Resource
{
    protected static ?string $model = CommercialSupplier::class;

    protected static string|UnitEnum|null $navigationGroup = PanelNavigationGroup::Comercial;

    protected static ?string $modelLabel = 'proveedor';

    protected static ?string $pluralModelLabel = 'Proveedores';

    protected static ?int $navigationSort = 6;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CommercialSupplierForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CommercialSuppliersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCommercialSuppliers::route('/'),
            'create' => CreateCommercialSupplier::route('/create'),
            'edit' => EditCommercialSupplier::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
