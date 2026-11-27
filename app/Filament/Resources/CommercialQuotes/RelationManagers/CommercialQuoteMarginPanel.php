<?php

namespace App\Filament\Resources\CommercialQuotes\RelationManagers;

use App\Enums\CommercialQuoteLineItemType;
use App\Enums\CommercialQuoteMarginOverrideType;
use App\Filament\Resources\CommercialQuotes\Schemas\CommercialQuoteMarginTab;
use App\Models\CommercialQuoteGroup;
use App\Models\CommercialQuoteLineItem;
use App\Support\CommercialQuoteLineItemMarginCalculator;
use App\Support\CommercialQuoteLineItemPersistence;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class CommercialQuoteMarginPanel extends RelationManager
{
    protected static string $relationship = 'groups';

    protected static ?string $title = 'Margen';

    /**
     * @var view-string
     */
    protected string $view = 'filament.resources.commercial-quotes.margin-panel';

    public function table(Table $table): Table
    {
        return $table->columns([])->paginated(false);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    /** @return Collection<int, CommercialQuoteGroup> */
    public function getGroupsProperty(): Collection
    {
        return $this->getOwnerRecord()
            ->groups()
            ->withCount('lineItems')
            ->with([
                'lineItems' => fn ($query) => $query
                    ->with(['product.category', 'personnel', 'contract', 'supplier', 'additionalCosts'])
                    ->orderBy('sort_order'),
            ])
            ->orderBy('sort_order')
            ->get();
    }

    public function createGroupAction(): Action
    {
        return Action::make('createGroup')
            ->label('Nuevo grupo')
            ->icon(Heroicon::OutlinedPlus)
            ->modalHeading('Nuevo grupo')
            ->modalSubmitActionLabel('Guardar')
            ->form(CommercialQuoteMarginTab::groupFormComponents())
            ->action(function (array $data): void {
                $sortOrder = (int) $this->getOwnerRecord()->groups()->max('sort_order') + 1;
                $this->getOwnerRecord()->groups()->create([
                    ...$data,
                    'sort_order' => $sortOrder,
                ]);
                $this->dispatchQuoteSummaryRefresh();
            });
    }

    public function editGroupAction(): Action
    {
        return Action::make('editGroup')
            ->icon(Heroicon::OutlinedPencilSquare)
            ->iconButton()
            ->tooltip('Editar grupo')
            ->modalHeading('Editar grupo')
            ->fillForm(function (array $arguments): array {
                return CommercialQuoteGroup::query()
                    ->findOrFail($arguments['groupId'])
                    ->attributesToArray();
            })
            ->form(CommercialQuoteMarginTab::groupFormComponents())
            ->action(function (array $arguments, array $data): void {
                CommercialQuoteGroup::query()
                    ->findOrFail($arguments['groupId'])
                    ->update($data);
                $this->dispatchQuoteSummaryRefresh();
            });
    }

    public function deleteGroupAction(): Action
    {
        return Action::make('deleteGroup')
            ->icon(Heroicon::OutlinedTrash)
            ->iconButton()
            ->color('danger')
            ->tooltip('Eliminar grupo')
            ->requiresConfirmation()
            ->modalHeading('Eliminar grupo')
            ->modalDescription('Se eliminarán también los elementos del grupo.')
            ->action(function (array $arguments): void {
                CommercialQuoteGroup::query()
                    ->findOrFail($arguments['groupId'])
                    ->delete();
                $this->dispatchQuoteSummaryRefresh();
            });
    }

    public function addProductAction(): Action
    {
        return Action::make('addProduct')
            ->label('Producto')
            ->icon(Heroicon::OutlinedCube)
            ->grouped()
            ->form(CommercialQuoteMarginTab::productFormComponents())
            ->modalHeading('Agregar producto')
            ->modalSubmitActionLabel('Agregar')
            ->action(function (array $arguments, array $data): void {
                $group = CommercialQuoteGroup::query()->findOrFail($arguments['groupId']);
                CommercialQuoteLineItemPersistence::createForGroup(
                    $group,
                    CommercialQuoteLineItemType::Product,
                    $data,
                );
                $this->dispatchQuoteSummaryRefresh();
            });
    }

    public function addPersonnelAction(): Action
    {
        return Action::make('addPersonnel')
            ->label('Recurso')
            ->icon(Heroicon::OutlinedUserGroup)
            ->grouped()
            ->form(CommercialQuoteMarginTab::personnelFormComponents())
            ->modalHeading('Agregar recurso')
            ->modalSubmitActionLabel('Agregar')
            ->action(function (array $arguments, array $data): void {
                $group = CommercialQuoteGroup::query()->findOrFail($arguments['groupId']);
                CommercialQuoteLineItemPersistence::createForGroup(
                    $group,
                    CommercialQuoteLineItemType::Personnel,
                    $data,
                );
                $this->dispatchQuoteSummaryRefresh();
            });
    }

    public function addContractAction(): Action
    {
        return Action::make('addContract')
            ->label('Contrato')
            ->icon(Heroicon::OutlinedDocumentText)
            ->grouped()
            ->form(CommercialQuoteMarginTab::contractFormComponents())
            ->modalHeading('Agregar contrato')
            ->modalSubmitActionLabel('Agregar')
            ->action(function (array $arguments, array $data): void {
                $group = CommercialQuoteGroup::query()->findOrFail($arguments['groupId']);
                CommercialQuoteLineItemPersistence::createForGroup(
                    $group,
                    CommercialQuoteLineItemType::Contract,
                    $data,
                );
                $this->dispatchQuoteSummaryRefresh();
            });
    }

    public function viewLineItemAction(): Action
    {
        return Action::make('viewLineItem')
            ->label('Ver detalles')
            ->link()
            ->modalHeading('Detalle del elemento')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Cerrar')
            ->fillForm(function (array $arguments): array {
                $item = CommercialQuoteLineItem::query()
                    ->with(['additionalCosts', 'product', 'personnel', 'contract', 'supplier'])
                    ->findOrFail($arguments['lineItemId']);

                return match ($item->type) {
                    CommercialQuoteLineItemType::Product => CommercialQuoteLineItemPersistence::productFormData($item),
                    default => $item->attributesToArray(),
                };
            })
            ->schema(function (array $arguments): array {
                $item = CommercialQuoteLineItem::query()->findOrFail($arguments['lineItemId']);

                return CommercialQuoteMarginTab::lineItemViewSchema($item);
            });
    }

    public function editLineItemAction(): Action
    {
        return Action::make('editLineItem')
            ->label('Editar')
            ->link()
            ->modalHeading('Editar elemento')
            ->fillForm(function (array $arguments): array {
                $item = CommercialQuoteLineItem::query()
                    ->with('additionalCosts')
                    ->findOrFail($arguments['lineItemId']);

                return match ($item->type) {
                    CommercialQuoteLineItemType::Product => CommercialQuoteLineItemPersistence::productFormData($item),
                    default => $item->attributesToArray(),
                };
            })
            ->form(function (array $arguments): array {
                $item = CommercialQuoteLineItem::query()->findOrFail($arguments['lineItemId']);

                return match ($item->type) {
                    CommercialQuoteLineItemType::Product => CommercialQuoteMarginTab::productFormComponents(),
                    CommercialQuoteLineItemType::Personnel => CommercialQuoteMarginTab::personnelFormComponents(),
                    CommercialQuoteLineItemType::Contract => CommercialQuoteMarginTab::contractFormComponents(),
                };
            })
            ->action(function (array $arguments, array $data): void {
                $item = CommercialQuoteLineItem::query()->findOrFail($arguments['lineItemId']);
                CommercialQuoteLineItemPersistence::updateLineItem($item, $data);
                $this->dispatchQuoteSummaryRefresh();
            });
    }

    public function deleteLineItemAction(): Action
    {
        return Action::make('deleteLineItem')
            ->label('Eliminar')
            ->link()
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Eliminar elemento')
            ->action(function (array $arguments): void {
                CommercialQuoteLineItem::query()
                    ->findOrFail($arguments['lineItemId'])
                    ->delete();
                $this->dispatchQuoteSummaryRefresh();
            });
    }

    public function overrideCustomerCostAction(): Action
    {
        return $this->makeMarginOverrideAction(
            name: 'overrideCustomerCost',
            heading: 'Costo de venta',
            label: 'costo de venta',
            type: CommercialQuoteMarginOverrideType::CustomerCost,
            isPercent: false,
        );
    }

    public function overrideCustomerAmountAction(): Action
    {
        return $this->makeMarginOverrideAction(
            name: 'overrideCustomerAmount',
            heading: 'Importe al cliente',
            label: 'importe',
            type: CommercialQuoteMarginOverrideType::CustomerAmount,
            isPercent: false,
        );
    }

    public function overrideMarginPercentAction(): Action
    {
        return $this->makeMarginOverrideAction(
            name: 'overrideMarginPercent',
            heading: 'Margen porcentual',
            label: 'porcentaje de margen',
            type: CommercialQuoteMarginOverrideType::MarginPercent,
            isPercent: true,
        );
    }

    public function overrideMarginAmountAction(): Action
    {
        return $this->makeMarginOverrideAction(
            name: 'overrideMarginAmount',
            heading: 'Margen en pesos',
            label: 'margen en pesos',
            type: CommercialQuoteMarginOverrideType::MarginAmount,
            isPercent: false,
        );
    }

    protected function makeMarginOverrideAction(
        string $name,
        string $heading,
        string $label,
        CommercialQuoteMarginOverrideType $type,
        bool $isPercent,
    ): Action {
        return Action::make($name)
            ->modalHeading($heading)
            ->modalSubmitActionLabel('Guardar')
            ->fillForm(function (array $arguments) use ($type): array {
                $item = CommercialQuoteLineItem::query()
                    ->with(['additionalCosts', 'personnel', 'contract'])
                    ->findOrFail($arguments['lineItemId']);
                $margin = CommercialQuoteLineItemMarginCalculator::forLineItem($item);

                $defaultValue = match ($type) {
                    CommercialQuoteMarginOverrideType::CustomerCost => $margin['default_customer_cost'],
                    CommercialQuoteMarginOverrideType::CustomerAmount => $margin['default_customer_amount'],
                    CommercialQuoteMarginOverrideType::MarginPercent => $margin['default_margin_percent'],
                    CommercialQuoteMarginOverrideType::MarginAmount => $margin['default_margin_amount'],
                };

                $manualValue = $item->manual_margin_override_type === $type
                    ? (float) $item->manual_margin_override_value
                    : $defaultValue;

                return [
                    'manual_value' => $manualValue,
                    'use_default' => $item->manual_margin_override_type !== $type,
                ];
            })
            ->form(function (array $arguments) use ($label, $type, $isPercent): array {
                $item = CommercialQuoteLineItem::query()
                    ->with(['additionalCosts', 'personnel', 'contract'])
                    ->findOrFail($arguments['lineItemId']);
                $margin = CommercialQuoteLineItemMarginCalculator::forLineItem($item);

                $defaultValue = match ($type) {
                    CommercialQuoteMarginOverrideType::CustomerCost => $margin['default_customer_cost'],
                    CommercialQuoteMarginOverrideType::CustomerAmount => $margin['default_customer_amount'],
                    CommercialQuoteMarginOverrideType::MarginPercent => $margin['default_margin_percent'],
                    CommercialQuoteMarginOverrideType::MarginAmount => $margin['default_margin_amount'],
                };

                $formattedDefault = $isPercent
                    ? number_format($defaultValue, 2).' %'
                    : '$'.number_format($defaultValue, 2);

                $hintLabel = $label;
                if ($type === CommercialQuoteMarginOverrideType::CustomerCost) {
                    $hintLabel = match ($item->type) {
                        CommercialQuoteLineItemType::Personnel => 'costo diario (USD)',
                        CommercialQuoteLineItemType::Contract => 'costo de venta',
                        default => 'costo de venta (por unidad)',
                    };
                }

                return [
                    Placeholder::make('default_hint')
                        ->label('')
                        ->content("El {$hintLabel} por defecto es: {$formattedDefault}"),
                    TextInput::make('manual_value')
                        ->label($isPercent ? 'Porcentaje manual' : 'Importe manual')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->maxValue($isPercent ? 99.99 : null)
                        ->suffix($isPercent ? '%' : null)
                        ->prefix($isPercent ? null : '$')
                        ->disabled(fn (Get $get): bool => (bool) $get('use_default')),
                    Toggle::make('use_default')
                        ->label('Usar valor calculado por defecto')
                        ->live()
                        ->default($item->manual_margin_override_type !== $type),
                ];
            })
            ->action(function (array $arguments, array $data) use ($type): void {
                $item = CommercialQuoteLineItem::query()->findOrFail($arguments['lineItemId']);

                if ($data['use_default'] ?? false) {
                    $item->update([
                        'manual_margin_override_type' => null,
                        'manual_margin_override_value' => null,
                    ]);
                    $this->dispatchQuoteSummaryRefresh();

                    return;
                }

                $item->update([
                    'manual_margin_override_type' => $type,
                    'manual_margin_override_value' => $data['manual_value'],
                ]);
                $this->dispatchQuoteSummaryRefresh();
            });
    }

    protected function dispatchQuoteSummaryRefresh(): void
    {
        $this->dispatch('commercial-quote-margin-updated');
    }
}
