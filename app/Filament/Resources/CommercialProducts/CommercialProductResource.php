<?php

namespace App\Filament\Resources\CommercialProducts;

use App\Enums\PanelNavigationGroup;
use App\Filament\Resources\CommercialProducts\Pages\CreateCommercialProduct;
use App\Filament\Resources\CommercialProducts\Pages\EditCommercialProduct;
use App\Filament\Resources\CommercialProducts\Pages\ListCommercialProducts;
use App\Filament\Resources\CommercialProducts\Schemas\CommercialProductForm;
use App\Filament\Resources\CommercialProducts\Tables\CommercialProductsTable;
use App\Models\CommercialProduct;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class CommercialProductResource extends Resource
{
    protected static ?string $model = CommercialProduct::class;

    protected static string|UnitEnum|null $navigationGroup = PanelNavigationGroup::Comercial;

    protected static ?string $modelLabel = 'producto';

    protected static ?string $pluralModelLabel = 'Productos';

    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CommercialProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CommercialProductsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCommercialProducts::route('/'),
            'create' => CreateCommercialProduct::route('/create'),
            'edit' => EditCommercialProduct::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
