<?php

namespace App\Filament\Resources\HelpdeskCoverages;

use App\Enums\PanelNavigationGroup;
use App\Filament\Resources\HelpdeskCoverages\Pages\CreateHelpdeskCoverage;
use App\Filament\Resources\HelpdeskCoverages\Pages\EditHelpdeskCoverage;
use App\Filament\Resources\HelpdeskCoverages\Pages\ListHelpdeskCoverages;
use App\Filament\Resources\HelpdeskCoverages\Schemas\HelpdeskCoverageForm;
use App\Filament\Resources\HelpdeskCoverages\Tables\HelpdeskCoveragesTable;
use App\Models\HelpdeskCoverage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class HelpdeskCoverageResource extends Resource
{
    protected static ?string $model = HelpdeskCoverage::class;

    protected static string|UnitEnum|null $navigationGroup = PanelNavigationGroup::Helpdesk;

    protected static ?string $modelLabel = 'cobertura';

    protected static ?string $pluralModelLabel = 'Coberturas';

    protected static ?string $navigationLabel = 'Coberturas';

    protected static ?int $navigationSort = 5;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return HelpdeskCoverageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HelpdeskCoveragesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHelpdeskCoverages::route('/'),
            'create' => CreateHelpdeskCoverage::route('/create'),
            'edit' => EditHelpdeskCoverage::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
