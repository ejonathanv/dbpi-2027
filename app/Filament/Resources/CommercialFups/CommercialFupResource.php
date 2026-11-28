<?php

namespace App\Filament\Resources\CommercialFups;

use App\Enums\PanelNavigationGroup;
use App\Filament\Resources\CommercialFups\Pages\EditCommercialFup;
use App\Filament\Resources\CommercialFups\Pages\ListCommercialFups;
use App\Filament\Resources\CommercialFups\Pages\ReviewCommercialFupAuthorization;
use App\Support\CommercialFupPanelUrls;
use App\Filament\Resources\CommercialFups\Schemas\CommercialFupForm;
use App\Filament\Resources\CommercialFups\Tables\CommercialFupsTable;
use App\Models\CommercialFup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class CommercialFupResource extends Resource
{
    protected static ?string $model = CommercialFup::class;

    protected static string|UnitEnum|null $navigationGroup = PanelNavigationGroup::Comercial;

    protected static ?string $modelLabel = 'FUP';

    protected static ?string $pluralModelLabel = "FUP's";

    protected static ?string $navigationLabel = "FUP's";

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $recordTitleAttribute = 'fup_number';

    public const EDIT_TAB_AUTORIZACIONES = CommercialFupPanelUrls::EDIT_TAB_AUTORIZACIONES;

    public static function form(Schema $schema): Schema
    {
        return CommercialFupForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CommercialFupsTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCommercialFups::route('/'),
            'edit' => EditCommercialFup::route('/{record}/edit'),
            'review-authorization' => ReviewCommercialFupAuthorization::route('/{record}/authorization/{authorizationRequest}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
