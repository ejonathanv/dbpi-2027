<?php

namespace App\Filament\Resources\CommercialQuotes\Schemas;

use App\Enums\CommercialQuoteLineItemType;
use App\Enums\HelpdeskContractValidityUnit;
use App\Filament\Resources\CommercialQuotes\Pages\EditCommercialQuote;
use App\Filament\Resources\CommercialQuotes\RelationManagers\CommercialQuoteMarginPanel;
use App\Models\CommercialPersonnel;
use App\Models\CommercialProduct;
use App\Models\CommercialQuoteLineItem;
use App\Models\CommercialSupplier;
use App\Models\HelpdeskContract;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\HtmlString;

class CommercialQuoteMarginTab
{
    /** @return array<int, mixed> */
    public static function tabSchema(string $operation): array
    {
        if ($operation !== 'edit') {
            return [
                Placeholder::make('margen_requires_save')
                    ->label('')
                    ->content('Guarde la cotización para crear grupos y agregar productos, recursos o contratos.'),
            ];
        }

        return [
            Livewire::make(CommercialQuoteMarginPanel::class, fn (Livewire $component): array => [
                'ownerRecord' => $component->getRecord(),
                'pageClass' => EditCommercialQuote::class,
            ])->lazyLoaded(),
        ];
    }

    /** @return array<int, mixed> */
    public static function groupFormComponents(): array
    {
        return [
            TextInput::make('title')
                ->label('Nombre o título del grupo')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),
            Toggle::make('hide_title_in_quote')
                ->label('Ocultar título del grupo en la cotización')
                ->helperText('No se verá el título en la cotización, pero sí los productos del grupo.')
                ->default(false),
            Toggle::make('hide_group_in_quote')
                ->label('Ocultar grupo en cotización')
                ->helperText('No se reflejará el monto del grupo en el importe total.')
                ->default(false),
            Toggle::make('hide_items_in_quote')
                ->label('Ocultar elementos de este grupo')
                ->helperText('Solo se verá el monto total del grupo, sin detalle de líneas.')
                ->default(false),
            Toggle::make('exclude_from_margin')
                ->label('No afectar margen')
                ->helperText('Las líneas de este grupo no afectarán el margen global.')
                ->default(false)
                ->columnSpanFull(),
        ];
    }

    /** @return array<int, mixed> */
    public static function productFormComponents(): array
    {
        return [
            self::productSelect()
                ->columnSpanFull(),
            TextInput::make('product_alias')
                ->label('Alias del producto')
                ->helperText('Solo se muestra en la cotización impresa.')
                ->maxLength(255)
                ->columnSpanFull(),
            Toggle::make('show_part_number')
                ->label('Mostrar número de parte')
                ->default(true),
            TextInput::make('product_quantity')
                ->label('Cantidad')
                ->numeric()
                ->required()
                ->minValue(0.0001)
                ->default(1),
            Section::make('Fotos y detalles')
                ->schema([
                    Toggle::make('show_product_image')
                        ->label('Mostrar imagen del producto')
                        ->helperText('Usa la primera imagen del catálogo como principal.')
                        ->default(true)
                        ->live(),
                    Placeholder::make('product_image_preview')
                        ->label('Vista previa')
                        ->content(fn (Get $get): HtmlString|string => self::productImagePreview($get))
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->columnSpanFull(),
            Toggle::make('hide_product_in_quote')
                ->label('Ocultar producto en la cotización')
                ->default(false),
            Toggle::make('product_exclude_from_margin')
                ->label('No afectar el margen')
                ->default(false),
            self::supplierSelect(),
            TextInput::make('supplier_cost')
                ->label('Costo del proveedor')
                ->numeric()
                ->minValue(0)
                ->default(0)
                ->prefix('$'),
            TextInput::make('supplier_discount_percent')
                ->label('% de descuento del proveedor')
                ->numeric()
                ->minValue(0)
                ->maxValue(100)
                ->suffix('%')
                ->default(0),
            TextInput::make('supplier_special_discount_percent')
                ->label('% de descuento especial del proveedor')
                ->numeric()
                ->minValue(0)
                ->maxValue(100)
                ->suffix('%')
                ->default(0),
            TextInput::make('customer_discount_percent')
                ->label('% de descuento al cliente')
                ->numeric()
                ->minValue(0)
                ->maxValue(100)
                ->suffix('%')
                ->default(0),
            Section::make('Costos adicionales por pieza')
                ->description('Envío, financiamiento, importación, logística, etc.')
                ->schema([
                    Repeater::make('additionalCosts')
                        ->label('')
                        ->addActionLabel('Agregar costo')
                        ->schema([
                            TextInput::make('concept')
                                ->label('Concepto')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('amount')
                                ->label('Importe por pieza')
                                ->numeric()
                                ->required()
                                ->minValue(0)
                                ->default(0)
                                ->prefix('$'),
                        ])
                        ->columns(2)
                        ->defaultItems(0)
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),
        ];
    }

    /** @return array<int, mixed> */
    public static function personnelFormComponents(): array
    {
        return [
            TextInput::make('personnel_quantity')
                ->label('Cantidad')
                ->numeric()
                ->required()
                ->minValue(0.0001)
                ->default(1),
            self::personnelSelect(),
            TextInput::make('personnel_days')
                ->label('Cantidad de días')
                ->numeric()
                ->integer()
                ->required()
                ->minValue(1)
                ->default(1),
            TextInput::make('customer_discount_percent')
                ->label('% de descuento al cliente')
                ->numeric()
                ->minValue(0)
                ->maxValue(100)
                ->suffix('%')
                ->default(0),
        ];
    }

    /** @return array<int, mixed> */
    public static function contractFormComponents(): array
    {
        return [
            self::contractSelect()->columnSpanFull(),
        ];
    }

    public static function productSelect(): Select
    {
        return Select::make('commercial_product_id')
            ->label('Producto')
            ->searchable()
            ->preload()
            ->required()
            ->live()
            ->options(fn (): array => CommercialProduct::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->pluck('name', 'id')
                ->all())
            ->getSearchResultsUsing(function (string $search): array {
                return CommercialProduct::query()
                    ->where('is_active', true)
                    ->where('name', 'like', "%{$search}%")
                    ->orderBy('name')
                    ->limit(50)
                    ->pluck('name', 'id')
                    ->all();
            })
            ->getOptionLabelUsing(fn ($value): ?string => CommercialProduct::query()->find($value)?->name)
            ->createOptionForm([
                TextInput::make('name')
                    ->label('Nombre del producto')
                    ->required()
                    ->maxLength(255),
                TextInput::make('part_number')
                    ->label('No. de parte')
                    ->maxLength(100),
                Toggle::make('is_active')
                    ->label('Activo')
                    ->default(true),
            ])
            ->createOptionUsing(function (array $data): int {
                return CommercialProduct::query()->create($data)->getKey();
            })
            ->native(false);
    }

    public static function supplierSelect(): Select
    {
        return Select::make('commercial_supplier_id')
            ->label('Proveedor')
            ->searchable()
            ->preload()
            ->options(fn (): array => CommercialSupplier::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->pluck('name', 'id')
                ->all())
            ->getSearchResultsUsing(function (string $search): array {
                return CommercialSupplier::query()
                    ->where('is_active', true)
                    ->where('name', 'like', "%{$search}%")
                    ->orderBy('name')
                    ->limit(50)
                    ->pluck('name', 'id')
                    ->all();
            })
            ->getOptionLabelUsing(fn ($value): ?string => CommercialSupplier::query()->find($value)?->name)
            ->createOptionForm([
                TextInput::make('name')
                    ->label('Nombre del proveedor')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Correo')
                    ->email()
                    ->maxLength(255),
                Toggle::make('is_active')
                    ->label('Activo')
                    ->default(true),
            ])
            ->createOptionUsing(function (array $data): int {
                return CommercialSupplier::query()->create($data)->getKey();
            })
            ->native(false);
    }

    public static function personnelSelect(): Select
    {
        return Select::make('commercial_personnel_id')
            ->label('Recurso')
            ->searchable()
            ->preload()
            ->required()
            ->options(fn (): array => CommercialPersonnel::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->pluck('name', 'id')
                ->all())
            ->getSearchResultsUsing(function (string $search): array {
                return CommercialPersonnel::query()
                    ->where('is_active', true)
                    ->where('name', 'like', "%{$search}%")
                    ->orderBy('name')
                    ->limit(50)
                    ->pluck('name', 'id')
                    ->all();
            })
            ->getOptionLabelUsing(fn ($value): ?string => CommercialPersonnel::query()->find($value)?->name)
            ->native(false);
    }

    public static function contractSelect(): Select
    {
        return Select::make('helpdesk_contract_id')
            ->label('Contrato')
            ->searchable()
            ->preload()
            ->required()
            ->options(fn (): array => HelpdeskContract::query()
                ->orderBy('name')
                ->pluck('name', 'id')
                ->all())
            ->getSearchResultsUsing(function (string $search): array {
                return HelpdeskContract::query()
                    ->where('name', 'like', "%{$search}%")
                    ->orderBy('name')
                    ->limit(50)
                    ->pluck('name', 'id')
                    ->all();
            })
            ->getOptionLabelUsing(fn ($value): ?string => HelpdeskContract::query()->find($value)?->name)
            ->createOptionForm([
                TextInput::make('name')
                    ->label('Nombre del contrato')
                    ->required()
                    ->maxLength(255),
                TextInput::make('validity_number')
                    ->label('Vigencia (número)')
                    ->numeric()
                    ->integer()
                    ->required()
                    ->minValue(1),
                Select::make('validity_unit')
                    ->label('Unidad de vigencia')
                    ->options(HelpdeskContractValidityUnit::class)
                    ->required()
                    ->native(false),
            ])
            ->createOptionUsing(function (array $data): int {
                return HelpdeskContract::query()->create($data)->getKey();
            })
            ->native(false);
    }

    public static function lineItemSummary(CommercialQuoteLineItem $item): string
    {
        return match ($item->type) {
            CommercialQuoteLineItemType::Product => $item->product_alias
                ?: $item->product?->name
                ?: 'Producto',
            CommercialQuoteLineItemType::Personnel => $item->personnel?->name ?? 'Recurso',
            CommercialQuoteLineItemType::Contract => $item->contract?->name ?? 'Contrato',
        };
    }

    public static function lineItemCompactDetail(CommercialQuoteLineItem $item): string
    {
        return match ($item->type) {
            CommercialQuoteLineItemType::Product => 'Cant. '.number_format((float) $item->product_quantity, 0, '.', ','),
            CommercialQuoteLineItemType::Personnel => number_format((float) $item->personnel_quantity, 0, '.', ',')
                .' × '.$item->personnel_days.' días',
            CommercialQuoteLineItemType::Contract => $item->contract?->validityLabel() ?? '—',
        };
    }

    /** @return array<int, mixed> */
    public static function lineItemViewSchema(CommercialQuoteLineItem $item): array
    {
        return match ($item->type) {
            CommercialQuoteLineItemType::Product => self::productViewSchema($item),
            CommercialQuoteLineItemType::Personnel => self::personnelViewSchema($item),
            CommercialQuoteLineItemType::Contract => self::contractViewSchema($item),
        };
    }

    /** @return array<int, mixed> */
    protected static function productViewSchema(CommercialQuoteLineItem $item): array
    {
        $additionalCosts = $item->additionalCosts
            ->map(fn ($cost): string => "{$cost->concept}: $".number_format((float) $cost->amount, 2))
            ->implode("\n") ?: '—';

        return [
            Placeholder::make('product_name')
                ->label('Producto')
                ->content($item->product?->name ?? '—'),
            Placeholder::make('product_alias')
                ->label('Alias')
                ->content($item->product_alias ?: '—'),
            Placeholder::make('product_quantity')
                ->label('Cantidad')
                ->content(number_format((float) $item->product_quantity, 4, '.', ',')),
            Placeholder::make('supplier_name')
                ->label('Proveedor')
                ->content($item->supplier?->name ?? '—'),
            Placeholder::make('supplier_cost')
                ->label('Costo proveedor')
                ->content('$'.number_format((float) $item->supplier_cost, 2)),
            Placeholder::make('discounts')
                ->label('Descuentos')
                ->content(sprintf(
                    'Proveedor: %s%% · Especial: %s%% · Cliente: %s%%',
                    number_format((float) $item->supplier_discount_percent, 2),
                    number_format((float) $item->supplier_special_discount_percent, 2),
                    number_format((float) $item->customer_discount_percent, 2),
                )),
            Placeholder::make('additional_costs')
                ->label('Costos adicionales (por pieza)')
                ->content($additionalCosts)
                ->columnSpanFull(),
            Placeholder::make('flags')
                ->label('Opciones de cotización')
                ->content(collect([
                    $item->show_part_number ? 'Mostrar no. de parte' : null,
                    $item->show_product_image ? 'Mostrar imagen' : null,
                    $item->hide_product_in_quote ? 'Oculto en cotización' : null,
                    $item->product_exclude_from_margin ? 'No afecta margen' : null,
                ])->filter()->implode(' · ') ?: '—')
                ->columnSpanFull(),
        ];
    }

    /** @return array<int, mixed> */
    protected static function personnelViewSchema(CommercialQuoteLineItem $item): array
    {
        return [
            Placeholder::make('personnel_name')
                ->label('Recurso')
                ->content($item->personnel?->name ?? '—'),
            Placeholder::make('personnel_quantity')
                ->label('Cantidad')
                ->content(number_format((float) $item->personnel_quantity, 4, '.', ',')),
            Placeholder::make('personnel_days')
                ->label('Días')
                ->content((string) $item->personnel_days),
        ];
    }

    /** @return array<int, mixed> */
    protected static function contractViewSchema(CommercialQuoteLineItem $item): array
    {
        return [
            Placeholder::make('contract_name')
                ->label('Contrato')
                ->content($item->contract?->name ?? '—'),
            Placeholder::make('contract_validity')
                ->label('Vigencia')
                ->content($item->contract?->validityLabel() ?? '—'),
            Placeholder::make('contract_description')
                ->label('Descripción')
                ->content($item->contract?->description ?: '—')
                ->columnSpanFull(),
        ];
    }

    protected static function productImagePreview(Get $get): HtmlString|string
    {
        if (! $get('show_product_image')) {
            return 'La imagen está desactivada para este renglón.';
        }

        $productId = $get('commercial_product_id');

        if (blank($productId)) {
            return 'Seleccione un producto.';
        }

        $url = CommercialProduct::query()->find($productId)?->primaryImageUrl();

        if (blank($url)) {
            return 'Sin imagen principal en el catálogo.';
        }

        return new HtmlString('<img src="'.e($url).'" alt="" class="max-h-48 rounded-lg border border-gray-200" />');
    }
}
