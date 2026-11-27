<?php

namespace App\Filament\Resources\HelpdeskContracts;

use App\Enums\PanelNavigationGroup;
use App\Filament\Resources\HelpdeskContracts\Pages\CreateHelpdeskContract;
use App\Filament\Resources\HelpdeskContracts\Pages\EditHelpdeskContract;
use App\Filament\Resources\HelpdeskContracts\Pages\ListHelpdeskContracts;
use App\Filament\Resources\HelpdeskContracts\Schemas\HelpdeskContractForm;
use App\Filament\Resources\HelpdeskContracts\Tables\HelpdeskContractsTable;
use App\Models\HelpdeskContract;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class HelpdeskContractResource extends Resource
{
    protected static ?string $model = HelpdeskContract::class;

    protected static string|UnitEnum|null $navigationGroup = PanelNavigationGroup::Helpdesk;

    protected static ?string $modelLabel = 'contrato';

    protected static ?string $pluralModelLabel = 'Contratos';

    protected static ?string $navigationLabel = 'Contratos';

    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return HelpdeskContractForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HelpdeskContractsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHelpdeskContracts::route('/'),
            'create' => CreateHelpdeskContract::route('/create'),
            'edit' => EditHelpdeskContract::route('/{record}/edit'),
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
