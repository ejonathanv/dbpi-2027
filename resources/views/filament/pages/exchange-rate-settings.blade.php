<x-filament-panels::page>
    <form wire:submit="save" class="max-w-xl space-y-4">
        <x-filament::section heading="Comercial">
            <div class="space-y-2">
                <label class="text-sm font-medium text-gray-950 dark:text-white">
                    Tipo de cambio (USD → MXN)
                </label>
                <input
                    type="number"
                    step="0.000001"
                    min="0"
                    wire:model="exchangeRate"
                    class="fi-input block w-full rounded-lg border-gray-300 shadow-sm dark:border-white/10 dark:bg-white/5"
                    required
                />
                @error('exchangeRate')
                    <p class="text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-4">
                <x-filament::button type="submit">
                    Guardar
                </x-filament::button>
            </div>
        </x-filament::section>
    </form>
</x-filament-panels::page>