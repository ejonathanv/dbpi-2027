@php
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
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900">
        @if ($status)
            <x-filament::badge :color="$status->getColor()" size="lg">{{ $status->getLabel() }}</x-filament::badge>
        @endif
        @if (filled($fup?->authorization_locked_at))
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Congelada desde {{ $fup->authorization_locked_at->format('d/m/Y H:i') }}
            </p>
        @endif
    </div>

    @forelse ($requests as $request)
        <article class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <p class="text-sm font-semibold">Solicitud del {{ $request->requested_at?->format('d/m/Y H:i') }}</p>
            <p class="text-xs text-gray-500">Solicitó: {{ $request->requester?->name ?? '—' }}</p>
            <ul class="mt-3 space-y-2">
                @foreach ($request->reviews as $review)
                    <li class="flex flex-wrap items-center justify-between gap-2 text-sm">
                        <span>{{ $review->user?->name ?? '—' }}</span>
                        <x-filament::badge :color="$review->status->getColor()">{{ $review->status->getLabel() }}</x-filament::badge>
                    </li>
                @endforeach
            </ul>
        </article>
    @empty
        <p class="text-sm text-gray-500">Sin solicitudes de autorización.</p>
    @endforelse
</div>