@php
    use App\Filament\Resources\CommercialFups\CommercialFupResource;
    use App\Enums\CommercialQuoteAuthorizationRequestStatus;
    use App\Enums\CommercialQuoteAuthorizationReviewStatus;

    /** @var \App\Models\CommercialFup|null $fup */
    $fup = $fup ?? null;

    if ($fup) {
        $fup->loadMissing(['authorizationRequests.requester', 'authorizationRequests.reviews.user']);
    }

    $status = $fup?->authorization_status;
    $requests = $fup?->authorizationRequests ?? collect();
@endphp

<div class="mx-auto max-w-4xl space-y-6">
    <div class="rounded-xl border border-gray-200 bg-gradient-to-br from-gray-50 to-white p-5 shadow-sm dark:border-white/10 dark:from-white/5 dark:to-gray-900">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    Estado de la FUP
                </p>
                @if ($status)
                    <div class="mt-2">
                        <x-filament::badge :color="$status->getColor()" size="lg">
                            {{ $status->getLabel() }}
                        </x-filament::badge>
                    </div>
                @endif
            </div>

            @if (filled($fup?->authorization_locked_at))
                <div class="text-end text-sm text-gray-600 dark:text-gray-400">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Congelada desde</p>
                    <p class="mt-1 font-medium tabular-nums text-gray-900 dark:text-white">
                        {{ $fup->authorization_locked_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
                    </p>
                </div>
            @endif
        </div>

        @if ($fup?->isEditingLocked())
            <p class="mt-4 text-sm text-gray-600 dark:text-gray-300">
                La carátula quedó congelada al completar la autorización.
            </p>
        @endif
    </div>

    <div>
        <div class="mb-4 flex items-center justify-between gap-2">
            <h3 class="text-base font-semibold text-gray-950 dark:text-white">Historial de solicitudes</h3>
            @if ($requests->isNotEmpty())
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $requests->count() }} {{ $requests->count() === 1 ? 'registro' : 'registros' }}
                </span>
            @endif
        </div>

        @forelse ($requests as $request)
            <article
                wire:key="fup-auth-request-{{ $request->id }}"
                @class([
                    'rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900',
                    'mt-4' => ! $loop->first,
                ])
            >
                <header class="flex flex-wrap items-start justify-between gap-3 border-b border-gray-100 px-5 py-4 dark:border-white/10">
                    <div>
                        <p class="text-sm font-semibold text-gray-950 dark:text-white">
                            Solicitud del {{ $request->requested_at?->timezone(config('app.timezone'))->format('d/m/Y') }}
                        </p>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                            {{ $request->requested_at?->timezone(config('app.timezone'))->format('h:i a') }}
                            · Solicitó: {{ $request->requester?->name ?? '—' }}
                        </p>
                    </div>
                    <x-filament::badge :color="$request->status->getColor()">
                        {{ $request->status->getLabel() }}
                    </x-filament::badge>
                </header>

                <div class="px-5 py-4">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        Autorizadores asignados
                    </p>

                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-white/10">
                        <table class="w-full min-w-[32rem] text-start text-sm">
                            <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500 dark:bg-white/5 dark:text-gray-400">
                                <tr>
                                    <th class="px-4 py-2.5 font-semibold">Nombre</th>
                                    <th class="px-4 py-2.5 font-semibold">Estado</th>
                                    <th class="px-4 py-2.5 font-semibold">Notas</th>
                                    <th class="px-4 py-2.5 text-end font-semibold">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                                @foreach ($request->reviews as $review)
                                    <tr wire:key="fup-auth-review-{{ $review->id }}">
                                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                            {{ $review->user?->name ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <x-filament::badge :color="$review->status->getColor()">
                                                {{ $review->status->getLabel() }}
                                            </x-filament::badge>
                                        </td>
                                        <td class="max-w-xs px-4 py-3 text-gray-600 dark:text-gray-300">
                                            @if (filled($review->notes))
                                                <span class="line-clamp-3">{{ $review->notes }}</span>
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-end">
                                            <div class="flex flex-wrap items-center justify-end gap-2">
                                                @if (
                                                    $fup
                                                    && $review->status === CommercialQuoteAuthorizationReviewStatus::Pending
                                                    && auth()->id() === $review->user_id
                                                )
                                                    <x-filament::link
                                                        :href="CommercialFupResource::getUrl('review-authorization', [
                                                            'record' => $fup,
                                                            'authorizationRequest' => $request,
                                                        ])"
                                                        color="primary"
                                                        size="sm"
                                                    >
                                                        Revisar
                                                    </x-filament::link>
                                                @endif

                                                @if (
                                                    $fup
                                                    && $review->status === CommercialQuoteAuthorizationReviewStatus::Pending
                                                    && $request->status === CommercialQuoteAuthorizationRequestStatus::Pending
                                                    && auth()->user()?->can('update', $fup)
                                                )
                                                    <x-filament::button
                                                        type="button"
                                                        size="xs"
                                                        color="gray"
                                                        icon="heroicon-m-paper-airplane"
                                                        wire:click="resendAuthorizationToReviewer({{ $review->id }})"
                                                        wire:loading.attr="disabled"
                                                        wire:target="resendAuthorizationToReviewer({{ $review->id }})"
                                                    >
                                                        <span wire:loading.remove wire:target="resendAuthorizationToReviewer({{ $review->id }})">
                                                            Reenviar solicitud
                                                        </span>
                                                        <span wire:loading wire:target="resendAuthorizationToReviewer({{ $review->id }})">
                                                            Enviando…
                                                        </span>
                                                    </x-filament::button>
                                                @endif

                                                @if (
                                                    $review->status !== CommercialQuoteAuthorizationReviewStatus::Pending
                                                    && $review->responded_at
                                                )
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ $review->responded_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 px-6 py-12 text-center dark:border-white/15 dark:bg-white/5">
                <p class="text-sm font-medium text-gray-900 dark:text-white">
                    Sin solicitudes de autorización
                </p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Al generar la FUP desde una cotización autorizada se envía automáticamente a validación.
                </p>
            </div>
        @endforelse
    </div>
</div>