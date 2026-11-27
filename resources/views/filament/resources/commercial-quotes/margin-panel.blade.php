@php
    use App\Enums\CommercialQuoteLineItemType;
    use App\Filament\Resources\CommercialQuotes\Schemas\CommercialQuoteMarginTab;

    $quote = $this->getOwnerRecord();
    $exchangeRate = number_format((float) $quote->exchange_rate, 6, '.', ',');
@endphp

<div class="fi-resource-relation-manager flex flex-col gap-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex shrink-0 items-center">
            {{ $this->createGroupAction }}
        </div>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Tipo de cambio (USD → MXN):
            <span class="font-semibold tabular-nums text-gray-950 dark:text-white">{{ $exchangeRate }}</span>
        </p>
    </div>

    @if ($this->groups->isEmpty())
        <p class="text-sm text-gray-500 dark:text-gray-400">
            No hay grupos. Agregue uno para comenzar a armar la cotización.
        </p>
    @endif

    <div class="flex flex-col gap-3">
            @foreach ($this->groups as $group)
                <div
                    wire:key="quote-group-{{ $group->id }}"
                    x-data="{ open: {{ $loop->first ? 'true' : 'false' }} }"
                    class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900"
                >
                    <div class="flex flex-wrap items-center gap-2 border-b border-gray-200 bg-gray-50 px-4 py-3 dark:border-white/10 dark:bg-white/5">
                        <button
                            type="button"
                            class="fi-icon-btn relative flex items-center justify-center rounded-lg p-2 text-gray-500 outline-none transition hover:bg-gray-500/10 dark:text-gray-400"
                            x-on:click="open = ! open"
                            x-bind:aria-expanded="open"
                        >
                            <x-filament::icon
                                icon="heroicon-m-chevron-down"
                                class="h-5 w-5 transition"
                                x-bind:class="open ? 'rotate-180' : ''"
                            />
                        </button>

                        <span class="text-base font-semibold text-gray-950 dark:text-white">
                            {{ $group->title }}
                        </span>

                        <x-filament::badge>
                            {{ $group->line_items_count }} {{ $group->line_items_count === 1 ? 'elemento' : 'elementos' }}
                        </x-filament::badge>

                        <div class="ms-auto flex flex-wrap items-center gap-1">
                            <x-filament::dropdown placement="bottom-end">
                                <x-slot name="trigger">
                                    <x-filament::button size="sm" color="primary" icon="heroicon-o-plus">
                                        Agregar
                                    </x-filament::button>
                                </x-slot>
                                <x-filament::dropdown.list>
                                    {{ ($this->addProductAction)(['groupId' => $group->id]) }}
                                    {{ ($this->addPersonnelAction)(['groupId' => $group->id]) }}
                                    {{ ($this->addContractAction)(['groupId' => $group->id]) }}
                                </x-filament::dropdown.list>
                            </x-filament::dropdown>
                            {{ ($this->editGroupAction)(['groupId' => $group->id]) }}
                            {{ ($this->deleteGroupAction)(['groupId' => $group->id]) }}
                        </div>
                    </div>

                    <div x-show="open" x-collapse class="space-y-4 px-4 py-3">
                        @if ($group->lineItems->isEmpty())
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Sin elementos. Use <strong>Agregar</strong> para incluir productos, recursos o contratos.
                            </p>
                        @else
                            @foreach ($group->lineItems as $item)
                                <div
                                    wire:key="quote-line-{{ $item->id }}"
                                    class="overflow-hidden rounded-lg border border-gray-200 dark:border-white/10"
                                >
                                    <div class="flex flex-wrap items-center gap-2 border-b border-gray-200 bg-gray-50 px-3 py-2 dark:border-white/10 dark:bg-white/5">
                                        <x-filament::badge>
                                            {{ $item->type->getLabel() }}
                                        </x-filament::badge>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ CommercialQuoteMarginTab::lineItemSummary($item) }}
                                        </span>
                                        @if ($item->type === CommercialQuoteLineItemType::Product)
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                Cant: {{ number_format((float) $item->product_quantity, 0, '.', ',') }}
                                            </span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                Categoría: {{ $item->product?->category?->name ?? '—' }}
                                            </span>
                                        @endif
                                        @if ($item->type === CommercialQuoteLineItemType::Product && $item->supplier)
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                Proveedor: {{ $item->supplier->name }}
                                            </span>
                                        @endif
                                        @if ($item->type !== CommercialQuoteLineItemType::Product)
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ CommercialQuoteMarginTab::lineItemCompactDetail($item) }}
                                            </span>
                                        @endif
                                        <div class="ms-auto flex flex-wrap items-center gap-x-3 gap-y-1">
                                            {{ ($this->viewLineItemAction)(['lineItemId' => $item->id]) }}
                                            {{ ($this->editLineItemAction)(['lineItemId' => $item->id]) }}
                                            {{ ($this->deleteLineItemAction)(['lineItemId' => $item->id]) }}
                                        </div>
                                    </div>

                                    @if ($item->type === CommercialQuoteLineItemType::Product)
                                        <div class="px-2 py-1">
                                            @include('filament.resources.commercial-quotes.partials.product-margin-table', ['item' => $item])
                                        </div>
                                    @endif
                                    @if ($item->type === CommercialQuoteLineItemType::Personnel)
                                        <div class="px-2 py-1">
                                            @include('filament.resources.commercial-quotes.partials.personnel-margin-table', ['item' => $item])
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            @endforeach
    </div>

    <x-filament-actions::modals />
</div>