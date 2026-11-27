<?php

namespace App\Filament\Resources\CommercialFups\Schemas;

use App\Enums\CommercialQuoteAttachmentType;
use App\Filament\Resources\CommercialFups\Pages\EditCommercialFup;
use App\Filament\Resources\CommercialFups\RelationManagers\CommercialFupContractsPanel;
use App\Filament\Resources\CommercialFups\Schemas\Tabs\CommercialFupAuthorizationTab;
use App\Filament\Resources\CommercialFups\Schemas\Tabs\CommercialFupCoverTab;
use App\Filament\Resources\CommercialQuotes\Pages\EditCommercialQuote;
use App\Filament\Resources\CommercialQuotes\RelationManagers\CommercialQuoteAttachmentsPanel;
use App\Filament\Resources\CommercialQuotes\RelationManagers\CommercialQuoteMarginPanel;
use App\Filament\Resources\CommercialQuotes\RelationManagers\CommercialQuoteSummaryPanel;
use App\Models\CommercialFup;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class CommercialFupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Tabs::make('FUP')
                    ->persistTabInQueryString('tab')
                    ->tabs([
                        Tab::make('FUP')
                            ->schema([
                                ViewField::make('fup_cover_preview')
                                    ->hiddenLabel()
                                    ->dehydrated(false)
                                    ->view('filament.resources.commercial-fups.fup-cover')
                                    ->viewData(fn (?CommercialFup $record): array => [
                                        'fup' => $record,
                                    ])
                                    ->columnSpanFull(),
                                ...CommercialFupCoverTab::schema(),
                            ]),
                        Tab::make('Cotización')
                            ->schema(fn (string $operation): array => self::linkedQuoteSummarySchema($operation)),
                        Tab::make('Margen')
                            ->schema(fn (string $operation): array => self::linkedQuoteMarginSchema($operation)),
                        Tab::make('Orden de compra')
                            ->schema(fn (string $operation): array => self::purchaseOrderSchema($operation)),
                        Tab::make('Archivos adjuntos')
                            ->schema(fn (string $operation): array => self::attachmentsSchema($operation)),
                        Tab::make('Contratos')
                            ->schema(fn (string $operation): array => self::contractsSchema($operation)),
                        Tab::make('Autorizaciones')
                            ->schema(fn (string $operation): array => CommercialFupAuthorizationTab::tabSchema($operation)),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    /** @return array<int, mixed> */
    protected static function linkedQuoteSummarySchema(string $operation): array
    {
        if ($operation !== 'edit') {
            return [];
        }

        return [
            Livewire::make(CommercialQuoteSummaryPanel::class, fn (Livewire $component): array => [
                'ownerRecord' => $component->getRecord()->quote,
                'pageClass' => EditCommercialQuote::class,
            ])->lazyLoaded(),
        ];
    }

    /** @return array<int, mixed> */
    protected static function linkedQuoteMarginSchema(string $operation): array
    {
        if ($operation !== 'edit') {
            return [];
        }

        return [
            Livewire::make(CommercialQuoteMarginPanel::class, fn (Livewire $component): array => [
                'ownerRecord' => $component->getRecord()->quote,
                'pageClass' => EditCommercialQuote::class,
            ])->lazyLoaded(),
        ];
    }

    /** @return array<int, mixed> */
    protected static function purchaseOrderSchema(string $operation): array
    {
        if ($operation !== 'edit') {
            return [];
        }

        return [
            Livewire::make(CommercialQuoteAttachmentsPanel::class, fn (Livewire $component): array => [
                'ownerRecord' => $component->getRecord()->quote,
                'pageClass' => EditCommercialQuote::class,
                'attachmentTypeFilter' => CommercialQuoteAttachmentType::PurchaseOrder->value,
            ])->lazyLoaded(),
        ];
    }

    /** @return array<int, mixed> */
    protected static function attachmentsSchema(string $operation): array
    {
        if ($operation !== 'edit') {
            return [];
        }

        return [
            Livewire::make(CommercialQuoteAttachmentsPanel::class, fn (Livewire $component): array => [
                'ownerRecord' => $component->getRecord()->quote,
                'pageClass' => EditCommercialQuote::class,
            ])->lazyLoaded(),
        ];
    }

    /** @return array<int, mixed> */
    protected static function contractsSchema(string $operation): array
    {
        if ($operation !== 'edit') {
            return [];
        }

        return [
            Livewire::make(CommercialFupContractsPanel::class, fn (Livewire $component): array => [
                'ownerRecord' => $component->getRecord(),
                'pageClass' => EditCommercialFup::class,
            ])->lazyLoaded(),
        ];
    }
}