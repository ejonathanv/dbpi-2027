<?php

namespace App\Filament\Resources\CommercialQuotes\RelationManagers;

use App\Enums\CommercialQuoteLineItemType;
use App\Filament\Resources\CommercialQuotes\Schemas\CommercialQuoteMarginTab;
use App\Models\CommercialQuoteLineItem;
use App\Support\CommercialQuoteLineItemPersistence;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CommercialQuoteLineItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'lineItems';

    protected static ?string $title = 'Elementos de los grupos';

    protected static ?string $modelLabel = 'elemento';

    protected static ?string $pluralModelLabel = 'Elementos';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query
                ->with(['group', 'product', 'personnel', 'contract'])
                ->orderBy('commercial_quote_group_id')
                ->orderBy('sort_order'))
            ->columns([
                TextColumn::make('group.title')
                    ->label('Grupo')
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge(),
                TextColumn::make('summary')
                    ->label('Descripción')
                    ->state(fn (CommercialQuoteLineItem $record): string => CommercialQuoteMarginTab::lineItemSummary($record)),
            ])
            ->defaultSort('sort_order')
            ->filters([
                SelectFilter::make('commercial_quote_group_id')
                    ->label('Grupo')
                    ->relationship('group', 'title'),
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options(CommercialQuoteLineItemType::class),
            ])
            ->headerActions([])
            ->recordActions([
                EditAction::make()
                    ->modalHeading('Editar elemento')
                    ->fillForm(fn (CommercialQuoteLineItem $record): array => match ($record->type) {
                        CommercialQuoteLineItemType::Product => CommercialQuoteLineItemPersistence::productFormData($record),
                        default => $record->attributesToArray(),
                    })
                    ->form(fn (CommercialQuoteLineItem $record): array => match ($record->type) {
                        CommercialQuoteLineItemType::Product => CommercialQuoteMarginTab::productFormComponents(),
                        CommercialQuoteLineItemType::Personnel => CommercialQuoteMarginTab::personnelFormComponents(),
                        CommercialQuoteLineItemType::Contract => CommercialQuoteMarginTab::contractFormComponents(),
                    })
                    ->using(fn (CommercialQuoteLineItem $record, array $data): CommercialQuoteLineItem => CommercialQuoteLineItemPersistence::updateLineItem($record, $data)),
                DeleteAction::make(),
            ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }
}
