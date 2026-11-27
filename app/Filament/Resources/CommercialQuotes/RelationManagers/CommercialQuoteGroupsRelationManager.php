<?php

namespace App\Filament\Resources\CommercialQuotes\RelationManagers;

use App\Enums\CommercialQuoteLineItemType;
use App\Filament\Resources\CommercialQuotes\Schemas\CommercialQuoteMarginTab;
use App\Models\CommercialQuoteGroup;
use App\Support\CommercialQuoteLineItemPersistence;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CommercialQuoteGroupsRelationManager extends RelationManager
{
    protected static string $relationship = 'groups';

    protected static ?string $title = 'Grupos';

    protected static ?string $modelLabel = 'grupo';

    protected static ?string $pluralModelLabel = 'Grupos';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components(CommercialQuoteMarginTab::groupFormComponents());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->modifyQueryUsing(fn ($query) => $query->withCount('lineItems')->orderBy('sort_order'))
            ->columns([
                TextColumn::make('title')
                    ->label('Grupo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('line_items_count')
                    ->label('Elementos')
                    ->badge(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->headerActions([
                CreateAction::make()
                    ->label('Agregar grupo')
                    ->modalHeading('Nuevo grupo')
                    ->createAnother(false),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('addProduct')
                        ->label('Producto')
                        ->icon(Heroicon::OutlinedCube)
                        ->form(CommercialQuoteMarginTab::productFormComponents())
                        ->modalHeading('Agregar producto al grupo')
                        ->modalSubmitActionLabel('Agregar')
                        ->action(function (CommercialQuoteGroup $record, array $data): void {
                            CommercialQuoteLineItemPersistence::createForGroup(
                                $record,
                                CommercialQuoteLineItemType::Product,
                                $data,
                            );
                        }),
                    Action::make('addPersonnel')
                        ->label('Recurso')
                        ->icon(Heroicon::OutlinedUserGroup)
                        ->form(CommercialQuoteMarginTab::personnelFormComponents())
                        ->modalHeading('Agregar recurso al grupo')
                        ->modalSubmitActionLabel('Agregar')
                        ->action(function (CommercialQuoteGroup $record, array $data): void {
                            CommercialQuoteLineItemPersistence::createForGroup(
                                $record,
                                CommercialQuoteLineItemType::Personnel,
                                $data,
                            );
                        }),
                    Action::make('addContract')
                        ->label('Contrato')
                        ->icon(Heroicon::OutlinedDocumentText)
                        ->form(CommercialQuoteMarginTab::contractFormComponents())
                        ->modalHeading('Agregar contrato al grupo')
                        ->modalSubmitActionLabel('Agregar')
                        ->action(function (CommercialQuoteGroup $record, array $data): void {
                            CommercialQuoteLineItemPersistence::createForGroup(
                                $record,
                                CommercialQuoteLineItemType::Contract,
                                $data,
                            );
                        }),
                ])
                    ->label('Agregar')
                    ->icon(Heroicon::OutlinedPlus)
                    ->button()
                    ->color('primary'),
                EditAction::make()
                    ->modalHeading('Editar grupo'),
                DeleteAction::make(),
            ]);
    }
}
