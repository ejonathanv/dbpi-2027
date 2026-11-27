<?php

namespace App\Filament\Resources\CommercialPersonnel;

use App\Enums\PanelNavigationGroup;
use App\Filament\Resources\CommercialPersonnel\Pages\CreateCommercialPersonnel;
use App\Filament\Resources\CommercialPersonnel\Pages\EditCommercialPersonnel;
use App\Filament\Resources\CommercialPersonnel\Pages\ListCommercialPersonnel;
use App\Filament\Resources\CommercialPersonnel\Schemas\CommercialPersonnelForm;
use App\Filament\Resources\CommercialPersonnel\Tables\CommercialPersonnelTable;
use App\Models\CommercialPersonnel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class CommercialPersonnelResource extends Resource
{
    protected static ?string $model = CommercialPersonnel::class;

    protected static string|UnitEnum|null $navigationGroup = PanelNavigationGroup::Comercial;

    protected static ?string $modelLabel = 'recurso';

    protected static ?string $pluralModelLabel = 'Recursos / Personal';

    protected static ?string $navigationLabel = 'Recursos / Personal';

    protected static ?int $navigationSort = 5;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CommercialPersonnelForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CommercialPersonnelTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCommercialPersonnel::route('/'),
            'create' => CreateCommercialPersonnel::route('/create'),
            'edit' => EditCommercialPersonnel::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
