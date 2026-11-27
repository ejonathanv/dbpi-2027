<?php

namespace App\Filament\Resources\CommercialQuotes;

use App\Enums\PanelNavigationGroup;
use App\Filament\Resources\CommercialQuotes\Pages\CreateCommercialQuote;
use App\Filament\Resources\CommercialQuotes\Pages\EditCommercialQuote;
use App\Filament\Resources\CommercialQuotes\Pages\ListCommercialQuotes;
use App\Filament\Resources\CommercialQuotes\Pages\ReviewCommercialQuoteAuthorization;
use App\Filament\Resources\CommercialQuotes\Schemas\CommercialQuoteForm;
use App\Filament\Resources\CommercialQuotes\Tables\CommercialQuotesTable;
use App\Models\CommercialQuote;
use App\Support\CommercialQuotePanelUrls;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class CommercialQuoteResource extends Resource
{
    protected static ?string $model = CommercialQuote::class;

    protected static string|UnitEnum|null $navigationGroup = PanelNavigationGroup::Comercial;

    protected static ?string $modelLabel = 'cotización';

    protected static ?string $pluralModelLabel = 'Cotizaciones';

    protected static ?string $navigationLabel = 'Cotizaciones';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCurrencyDollar;

    protected static ?string $recordTitleAttribute = 'quote_number';

    public const EDIT_TAB_AUTORIZACIONES = CommercialQuotePanelUrls::EDIT_TAB_AUTORIZACIONES;

    public static function getEditAuthorizationTabUrl(CommercialQuote $quote): string
    {
        return CommercialQuotePanelUrls::editAuthorizationTab($quote);
    }

    public static function form(Schema $schema): Schema
    {
        return CommercialQuoteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CommercialQuotesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCommercialQuotes::route('/'),
            'create' => CreateCommercialQuote::route('/create'),
            'edit' => EditCommercialQuote::route('/{record}/edit'),
            'review-authorization' => ReviewCommercialQuoteAuthorization::route('/{record}/authorization/{authorizationRequest}'),
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
