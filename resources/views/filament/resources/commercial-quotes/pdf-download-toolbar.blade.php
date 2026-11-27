@php
    use App\Support\CommercialQuotePdfExporter;

    /** @var \App\Models\CommercialQuote|null $quote */
    $urls = [
        CommercialQuotePdfExporter::VARIANT_COTIZACION => $downloadUrls['cotizacion'] ?? '#',
        CommercialQuotePdfExporter::VARIANT_MARGEN => $downloadUrls['margen'] ?? '#',
        CommercialQuotePdfExporter::VARIANT_TODO => $downloadUrls['todo'] ?? '#',
    ];
@endphp

<div class="ms-auto flex shrink-0 items-center self-center">
    <x-filament::dropdown placement="bottom-end">
        <x-slot name="trigger">
            <x-filament::button
                size="sm"
                color="gray"
                icon="heroicon-o-arrow-down-tray"
                tag="button"
            >
                Descargar PDF
            </x-filament::button>
        </x-slot>

        <x-filament::dropdown.list>
            <x-filament::dropdown.list.item
                :href="$urls[CommercialQuotePdfExporter::VARIANT_COTIZACION]"
                tag="a"
                icon="heroicon-o-document-text"
            >
                Descargar Cotización
            </x-filament::dropdown.list.item>
            <x-filament::dropdown.list.item
                :href="$urls[CommercialQuotePdfExporter::VARIANT_MARGEN]"
                tag="a"
                icon="heroicon-o-chart-bar"
            >
                Descargar Margen
            </x-filament::dropdown.list.item>
            <x-filament::dropdown.list.item
                :href="$urls[CommercialQuotePdfExporter::VARIANT_TODO]"
                tag="a"
                icon="heroicon-o-document-duplicate"
            >
                Descargar Todo
            </x-filament::dropdown.list.item>
        </x-filament::dropdown.list>
    </x-filament::dropdown>
</div>