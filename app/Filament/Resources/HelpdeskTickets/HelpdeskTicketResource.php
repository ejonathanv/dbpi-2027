<?php

namespace App\Filament\Resources\HelpdeskTickets;

use App\Enums\PanelNavigationGroup;
use App\Filament\Resources\HelpdeskTickets\Pages\CreateHelpdeskTicket;
use App\Filament\Resources\HelpdeskTickets\Pages\EditHelpdeskTicket;
use App\Filament\Resources\HelpdeskTickets\Pages\ListHelpdeskTickets;
use App\Filament\Resources\HelpdeskTickets\Schemas\HelpdeskTicketForm;
use App\Filament\Resources\HelpdeskTickets\Tables\HelpdeskTicketsTable;
use App\Models\HelpdeskTicket;
use App\Models\User;
use App\Support\HelpdeskRecordAccess;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class HelpdeskTicketResource extends Resource
{
    protected static ?string $model = HelpdeskTicket::class;

    protected static string|UnitEnum|null $navigationGroup = PanelNavigationGroup::Helpdesk;

    protected static ?string $modelLabel = 'ticket';

    protected static ?string $pluralModelLabel = 'Tickets';

    protected static ?string $navigationLabel = 'Tickets';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static ?string $recordTitleAttribute = 'subject';

    public static function form(Schema $schema): Schema
    {
        return HelpdeskTicketForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HelpdeskTicketsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHelpdeskTickets::route('/'),
            'create' => CreateHelpdeskTicket::route('/create'),
            'edit' => EditHelpdeskTicket::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();

        if ($user instanceof User) {
            HelpdeskRecordAccess::scopeTicketsQuery($query, $user);
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
            HelpdeskRecordAccess::scopeTicketsQuery($query, $user);
        }

        return $query;
    }
}
