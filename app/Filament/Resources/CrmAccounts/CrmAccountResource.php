<?php

namespace App\Filament\Resources\CrmAccounts;

use App\Filament\Resources\CrmAccounts\Pages\CreateCrmAccount;
use App\Filament\Resources\CrmAccounts\Pages\EditCrmAccount;
use App\Filament\Resources\CrmAccounts\Pages\ListCrmAccounts;
use App\Filament\Resources\CrmAccounts\RelationManagers\ContactsRelationManager;
use App\Filament\Resources\CrmAccounts\Schemas\CrmAccountForm;
use App\Filament\Resources\CrmAccounts\Tables\CrmAccountsTable;
use App\Models\CrmAccount;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class CrmAccountResource extends Resource
{
    protected static ?string $model = CrmAccount::class;

    protected static string|UnitEnum|null $navigationGroup = 'CRM';

    protected static ?string $modelLabel = 'cuenta';

    protected static ?string $pluralModelLabel = 'Cuentas';

    protected static ?string $navigationLabel = 'Cuentas';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CrmAccountForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CrmAccountsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ContactsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCrmAccounts::route('/'),
            'create' => CreateCrmAccount::route('/create'),
            'edit' => EditCrmAccount::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
