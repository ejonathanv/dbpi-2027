@php
    $cover = $this->frozenCover;
    $review = $this->getReviewForCurrentUser();
@endphp

<x-filament-panels::page>
    <div class="space-y-6">
        @if ($cover !== [])
            @include('filament.resources.commercial-fups.fup-cover', [
                'cover' => $cover,
                'fup' => null,
            ])
        @else
            <x-filament::section>
                <p class="text-sm text-gray-500">Sin carátula en el congelado de autorización.</p>
            </x-filament::section>
        @endif

        @if ($review && $review->status->value === 'pending')
            <div class="grid gap-6 lg:grid-cols-3">
                <x-filament::section heading="Autorizar">
                    <textarea
                        wire:model="approveNotes"
                        rows="3"
                        class="fi-input block w-full rounded-lg border-gray-300 p-3 text-sm shadow-sm dark:border-white/10 dark:bg-white/5"
                        placeholder="Comentario opcional"
                    ></textarea>
                    <x-filament::button wire:click="approve" color="success" class="mt-3 w-full">
                        Autorizar
                    </x-filament::button>
                </x-filament::section>

                <x-filament::section heading="Rechazar">
                    <textarea
                        wire:model="rejectNotes"
                        rows="3"
                        class="fi-input block w-full rounded-lg border-gray-300 p-3 text-sm shadow-sm dark:border-white/10 dark:bg-white/5"
                        placeholder="Motivo del rechazo"
                    ></textarea>
                    <x-filament::button wire:click="reject" color="danger" class="mt-3 w-full">
                        Rechazar
                    </x-filament::button>
                </x-filament::section>

                <x-filament::section heading="Sugerir modificaciones">
                    <textarea
                        wire:model="changesNotes"
                        rows="3"
                        class="fi-input block w-full rounded-lg border-gray-300 p-3 text-sm shadow-sm dark:border-white/10 dark:bg-white/5"
                        placeholder="Indique los cambios sugeridos"
                    ></textarea>
                    <x-filament::button wire:click="suggestChanges" color="info" class="mt-3 w-full">
                        Sugerir modificaciones
                    </x-filament::button>
                </x-filament::section>
            </div>
        @elseif ($review)
            <x-filament::section>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Ya respondiste esta solicitud: <strong>{{ $review->status->getLabel() }}</strong>
                    @if (filled($review->notes))
                        — {{ $review->notes }}
                    @endif
                </p>
            </x-filament::section>
        @else
            <x-filament::section>
                <p class="text-sm text-gray-600 dark:text-gray-400">No estás asignado como autorizador en esta solicitud.</p>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>