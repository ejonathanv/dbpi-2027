<?php

namespace App\Filament\Resources\CrmContacts;

use App\Enums\PanelNavigationGroup;
use App\Filament\Resources\CrmContacts\Pages\CreateCrmContact;
use App\Filament\Resources\CrmContacts\Pages\EditCrmContact;
use App\Filament\Resources\CrmContacts\Pages\ListCrmContacts;
use App\Filament\Resources\CrmContacts\Schemas\CrmContactForm;
use App\Filament\Resources\CrmContacts\Tables\CrmContactsTable;
use App\Models\CrmContact;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class CrmContactResource extends Resource
{
    protected static ?string $model = CrmContact::class;

    protected static string|UnitEnum|null $navigationGroup = PanelNavigationGroup::Crm;

    protected static ?string $modelLabel = 'contacto';

    protected static ?string $pluralModelLabel = 'Contactos';

    protected static ?string $navigationLabel = 'Contactos';

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    public static function form(Schema $schema): Schema
    {
        return CrmContactForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CrmContactsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCrmContacts::route('/'),
            'create' => CreateCrmContact::route('/create'),
            'edit' => EditCrmContact::route('/{record}/edit'),
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
