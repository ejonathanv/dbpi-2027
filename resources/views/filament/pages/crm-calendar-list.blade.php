@php
    /** @var \Illuminate\Support\Collection<string, \Illuminate\Support\Collection<int, \App\Models\CrmCalendarEvent>> $eventsByDay */
@endphp

<x-filament-panels::page>
    <div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-gray-900">
        <h2 class="text-base font-semibold text-gray-950 dark:text-white">
            {{ $monthLabel }}
        </h2>
        <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($eventsByDay as $day => $dayEvents)
                <div class="rounded-lg border border-gray-100 p-3 dark:border-white/10">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        {{ \Illuminate\Support\Carbon::parse($day)->translatedFormat('d M') }}
                    </p>
                    <ul class="mt-2 space-y-2">
                        @foreach ($dayEvents as $event)
                            <li class="text-sm">
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $event->title }}</span>
                                @unless ($event->all_day)
                                    <span class="block text-xs text-gray-500">
                                        {{ $event->starts_at->format('H:i') }}
                                        @if ($event->ends_at)
                                            – {{ $event->ends_at->format('H:i') }}
                                        @endif
                                    </span>
                                @endunless
                                @if ($event->account)
                                    <span class="block text-xs text-gray-500">{{ $event->account->name }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @empty
                <p class="col-span-full text-sm text-gray-500 dark:text-gray-400">
                    No hay eventos este mes. Usa «Nuevo evento» para agendar.
                </p>
            @endforelse
        </div>
    </div>

    {{ $this->table }}
</x-filament-panels::page>