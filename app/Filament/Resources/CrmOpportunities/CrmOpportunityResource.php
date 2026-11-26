<?php

namespace App\Filament\Resources\CrmOpportunities;

use App\Enums\PanelNavigationGroup;
use App\Filament\Resources\CrmOpportunities\Pages\CreateCrmOpportunity;
use App\Filament\Resources\CrmOpportunities\Pages\EditCrmOpportunity;
use App\Filament\Resources\CrmOpportunities\Pages\ListCrmOpportunities;
use App\Filament\Resources\CrmOpportunities\Schemas\CrmOpportunityForm;
use App\Filament\Resources\CrmOpportunities\Tables\CrmOpportunitiesTable;
use App\Models\CrmOpportunity;
use App\Models\User;
use App\Support\CrmRecordAccess;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class CrmOpportunityResource extends Resource
{
    protected static ?string $model = CrmOpportunity::class;

    protected static string|UnitEnum|null $navigationGroup = PanelNavigationGroup::Crm;

    protected static ?string $modelLabel = 'oportunidad';

    protected static ?string $pluralModelLabel = 'Oportunidades';

    protected static ?string $navigationLabel = 'Oportunidades';

    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CrmOpportunityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CrmOpportunitiesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCrmOpportunities::route('/'),
            'create' => CreateCrmOpportunity::route('/create'),
            'edit' => EditCrmOpportunity::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();

        if ($user instanceof User) {
            CrmRecordAccess::scopeOpportunitiesQuery($query, $user);
        }

        return $query;
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        $query = parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);

        $user = auth()->user();

        if ($user instanceof User) {
            CrmRecordAccess::scopeOpportunitiesQuery($query, $user);
        }

        return $query;
    }
}
