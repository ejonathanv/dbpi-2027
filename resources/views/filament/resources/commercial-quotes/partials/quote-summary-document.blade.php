@php
    /** @var array<string, mixed> $preview */
    /** @var callable(float): string $money */
@endphp

<div class="mx-auto max-w-4xl rounded-xl border border-gray-200 bg-white p-8 text-[11px] leading-snug text-gray-900 shadow-sm dark:border-white/10 dark:bg-gray-900 dark:text-gray-100">
    <div class="mb-6 text-[10px] font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
        <img
            src="{{ $preview['issuer_logo_url'] ?? '' }}"
            alt="Dynamic Communications"
            class="mb-3 h-12 w-auto max-w-[14rem] object-contain object-left"
        />
        @foreach ($preview['issuer_address_lines'] ?? [] as $line)
            <div>{{ $line }}</div>
        @endforeach
    </div>

    @include('filament.resources.commercial-quotes.partials.quote-summary-body', ['preview' => $preview, 'money' => $money])
</div>