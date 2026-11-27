@php
    $preview = $this->frozenPreview;
    $marginLines = $this->frozenMarginLines;
    $totals = $this->frozenTotals;
    $review = $this->getReviewForCurrentUser();
    $money = static fn (float $value): string => '$' . number_format($value, 2, '.', ',');
@endphp

<x-filament-panels::page>
    <div class="space-y-6">
        @include('filament.resources.commercial-quotes.partials.quote-summary-document', [
            'preview' => $preview,
            'money' => $money,
        ])

        <x-filament::section heading="Margen (congelado al solicitar autorización)">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[40rem] text-start text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-white/10">
                            <th class="px-2 py-2 font-semibold">Grupo</th>
                            <th class="px-2 py-2 font-semibold">Partida</th>
                            <th class="px-2 py-2 text-end font-semibold">Importe cliente</th>
                            <th class="px-2 py-2 text-end font-semibold">Margen %</th>
                            <th class="px-2 py-2 text-end font-semibold">Margen $</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($marginLines as $line)
                            @php
                                $margin = $line['margin'] ?? [];
                            @endphp
                            <tr class="border-b border-gray-100 dark:border-white/5">
                                <td class="px-2 py-2">{{ $line['group_title'] ?? '—' }}</td>
                                <td class="px-2 py-2">{{ $line['label'] ?? '—' }}</td>
                                <td class="px-2 py-2 text-end tabular-nums">{{ $money((float) ($margin['customer_amount'] ?? 0)) }}</td>
                                <td class="px-2 py-2 text-end tabular-nums">{{ number_format((float) ($margin['margin_percent'] ?? 0), 2) }}%</td>
                                <td class="px-2 py-2 text-end tabular-nums">{{ $money((float) ($margin['margin_amount'] ?? 0)) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-2 py-4 text-gray-500">Sin partidas en el congelado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if (filled($totals))
                <div class="mt-4 grid gap-2 text-sm sm:grid-cols-3">
                    <div><span class="text-gray-500">Importe cliente:</span> <strong>{{ $money((float) ($totals['customer_amount'] ?? 0)) }}</strong></div>
                    <div><span class="text-gray-500">Margen %:</span> <strong>{{ number_format((float) ($totals['margin_percent'] ?? 0), 2) }}%</strong></div>
                    <div><span class="text-gray-500">Margen $:</span> <strong>{{ $money((float) ($totals['margin_amount'] ?? 0)) }}</strong></div>
                </div>
            @endif
        </x-filament::section>

        @if ($review && $review->status->value === 'pending')
            <div class="grid gap-6 lg:grid-cols-3">
                <x-filament::section heading="Autorizar">
                    <textarea
                        wire:model="approveNotes"
                        rows="3"
                        class="fi-input block w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-white/10 dark:bg-white/5"
                        placeholder="Notas opcionales"
                    ></textarea>
                    <x-filament::button wire:click="approve" color="success" class="mt-3 w-full">
                        Autorizar
                    </x-filament::button>
                </x-filament::section>

                <x-filament::section heading="Rechazar">
                    <textarea
                        wire:model="rejectNotes"
                        rows="3"
                        class="fi-input block w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-white/10 dark:bg-white/5"
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
                        class="fi-input block w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-white/10 dark:bg-white/5"
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