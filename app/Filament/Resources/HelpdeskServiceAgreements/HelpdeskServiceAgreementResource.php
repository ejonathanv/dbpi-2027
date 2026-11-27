<?php

namespace App\Filament\Resources\HelpdeskServiceAgreements;

use App\Enums\PanelNavigationGroup;
use App\Filament\Resources\HelpdeskServiceAgreements\Pages\CreateHelpdeskServiceAgreement;
use App\Filament\Resources\HelpdeskServiceAgreements\Pages\EditHelpdeskServiceAgreement;
use App\Filament\Resources\HelpdeskServiceAgreements\Pages\ListHelpdeskServiceAgreements;
use App\Filament\Resources\HelpdeskServiceAgreements\Schemas\HelpdeskServiceAgreementForm;
use App\Filament\Resources\HelpdeskServiceAgreements\Tables\HelpdeskServiceAgreementsTable;
use App\Models\HelpdeskServiceAgreement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class HelpdeskServiceAgreementResource extends Resource
{
    protected static ?string $model = HelpdeskServiceAgreement::class;

    protected static string|UnitEnum|null $navigationGroup = PanelNavigationGroup::Helpdesk;

    protected static ?string $modelLabel = 'acuerdo de servicio';

    protected static ?string $pluralModelLabel = 'Acuerdos de servicio (SLA)';

    protected static ?string $navigationLabel = 'Acuerdos SLA';

    protected static ?int $navigationSort = 6;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return HelpdeskServiceAgreementForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HelpdeskServiceAgreementsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHelpdeskServiceAgreements::route('/'),
            'create' => CreateHelpdeskServiceAgreement::route('/create'),
            'edit' => EditHelpdeskServiceAgreement::route('/{record}/edit'),
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
