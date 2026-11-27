@php
  /** @var string|null $fupNumber */
  $fupNumber = $fupNumber ?? null;
@endphp

<div class="mb-4 flex w-full items-start justify-between gap-4 border-b border-gray-200 pb-3 dark:border-white/10">
    <img
        src="{{ asset('img/svg/logo-dynacom.svg') }}"
        alt="Dynamic Communications"
        class="h-10 w-auto max-w-[12rem] object-contain object-left"
    />
    <div class="text-end">
        <div class="text-xs font-bold uppercase tracking-wide text-gray-700 dark:text-gray-300">Forma única de pedido</div>
        @if (filled($fupNumber))
            <div class="mt-1 text-base font-semibold text-gray-900 dark:text-gray-100">#{{ $fupNumber }}</div>
        @endif
    </div>
</div>