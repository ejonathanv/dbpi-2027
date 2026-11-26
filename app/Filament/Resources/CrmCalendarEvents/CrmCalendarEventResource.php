<?php

namespace App\Filament\Resources\CrmCalendarEvents;

use App\Enums\PanelNavigationGroup;
use App\Filament\Resources\CrmCalendarEvents\Pages\CreateCrmCalendarEvent;
use App\Filament\Resources\CrmCalendarEvents\Pages\EditCrmCalendarEvent;
use App\Filament\Resources\CrmCalendarEvents\Pages\ListCrmCalendarEvents;
use App\Filament\Resources\CrmCalendarEvents\Schemas\CrmCalendarEventForm;
use App\Filament\Resources\CrmCalendarEvents\Tables\CrmCalendarEventsTable;
use App\Models\CrmCalendarEvent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CrmCalendarEventResource extends Resource
{
    protected static ?string $model = CrmCalendarEvent::class;

    protected static string|UnitEnum|null $navigationGroup = PanelNavigationGroup::Crm;

    protected static ?string $modelLabel = 'evento';

    protected static ?string $pluralModelLabel = 'Eventos';

    protected static ?string $navigationLabel = 'Calendario';

    protected static ?int $navigationSort = 5;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return CrmCalendarEventForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CrmCalendarEventsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCrmCalendarEvents::route('/'),
            'create' => CreateCrmCalendarEvent::route('/create'),
            'edit' => EditCrmCalendarEvent::route('/{record}/edit'),
        ];
    }
}
