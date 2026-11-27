<?php

namespace App\Filament\Resources\CommercialQuotes\RelationManagers;

use App\Enums\CommercialQuoteLineItemType;
use App\Filament\Resources\CommercialQuotes\Schemas\CommercialQuoteMarginTab;
use App\Models\CommercialQuoteGroup;
use App\Models\CommercialQuoteLineItem;
use App\Support\CommercialQuoteLineItemPersistence;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
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
            ->label('Agregar grupo')
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
            });
    }
}
