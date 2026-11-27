<x-filament-panels::page>
    <form wire:submit="saveAuthorizers" class="space-y-6">
        {{ $this->form }}

        <x-filament::button type="submit">
            Guardar autorizadores
        </x-filament::button>
    </form>
</x-filament-panels::page>