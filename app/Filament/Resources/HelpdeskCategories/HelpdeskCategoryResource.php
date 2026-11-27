<?php

namespace App\Filament\Resources\HelpdeskCategories;

use App\Enums\PanelNavigationGroup;
use App\Filament\Resources\HelpdeskCategories\Pages\CreateHelpdeskCategory;
use App\Filament\Resources\HelpdeskCategories\Pages\EditHelpdeskCategory;
use App\Filament\Resources\HelpdeskCategories\Pages\ListHelpdeskCategories;
use App\Filament\Resources\HelpdeskCategories\Schemas\HelpdeskCategoryForm;
use App\Filament\Resources\HelpdeskCategories\Tables\HelpdeskCategoriesTable;
use App\Models\HelpdeskCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class HelpdeskCategoryResource extends Resource
{
    protected static ?string $model = HelpdeskCategory::class;

    protected static string|UnitEnum|null $navigationGroup = PanelNavigationGroup::Helpdesk;

    protected static ?string $modelLabel = 'categoría';

    protected static ?string $pluralModelLabel = 'Categorías';

    protected static ?string $navigationLabel = 'Categorías';

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    public static function form(Schema $schema): Schema
    {
        return HelpdeskCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HelpdeskCategoriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHelpdeskCategories::route('/'),
            'create' => CreateHelpdeskCategory::route('/create'),
            'edit' => EditHelpdeskCategory::route('/{record}/edit'),
        ];
    }
}
