<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            {{ $moduleLabel }}
        </x-slot>

        <p class="text-sm text-gray-600 dark:text-gray-400">
            Módulo en construcción. El menú de este módulo solo es visible para cuentas con permiso de
            <strong>ver</strong> o superior.
        </p>
    </x-filament::section>
</x-filament-panels::page>