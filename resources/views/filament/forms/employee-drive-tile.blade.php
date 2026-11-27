@php
    use Illuminate\Support\Facades\Storage;

    $path = $path ?? null;
    $kind = $kind ?? 'file';
    $title = filled($title ?? null) ? $title : ($originalName ?? null);

    if ($kind === 'photo') {
        $displayName = filled($title) ? $title : 'Foto del empleado';
    } else {
        $displayName = filled($title) ? $title : ($path ? basename($path) : 'Sin archivo');
        if (preg_match('/^[a-zA-Z0-9]{20,}(\.[a-z]+)?$/', $displayName)) {
            $displayName = filled($originalName ?? null) ? $originalName : 'Archivo';
        }
    }

    $url = filled($previewUrl ?? null)
        ? $previewUrl
        : (filled($path) ? '/storage/'.ltrim(str_replace('\\', '/', $path), '/') : null);
    $extension = $path ? strtolower(pathinfo($path, PATHINFO_EXTENSION)) : null;

    $mimeType = null;
    if (filled($path) && Storage::disk('public')->exists($path)) {
        try {
            $mimeType = Storage::disk('public')->mimeType($path);
        } catch (\Throwable) {
            $mimeType = null;
        }
    }

    $isImage = $mimeType
        ? str_starts_with($mimeType, 'image/')
        : in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'], true);

    $isPdf = ($mimeType === 'application/pdf') || $extension === 'pdf';

    $imageFit = in_array($imageFit ?? 'cover', ['cover', 'contain'], true) ? ($imageFit ?? 'cover') : 'cover';

    $uploadedLabel = null;
    if (filled($uploadedAt ?? null)) {
        $uploadedLabel = \Illuminate\Support\Carbon::parse($uploadedAt)
            ->timezone(config('app.timezone'))
            ->format('d/m/Y, h:i a');
    }
@endphp

<div @class([
    'employee-drive-tile',
    'employee-drive-tile--thumb-only' => ($layout ?? 'full') === 'thumb',
    'employee-drive-tile--fit-contain' => $imageFit === 'contain',
])>
  <div class="employee-drive-tile__preview-wrap">
  <a
      href="{{ $url ?: '#' }}"
      @class([
          'employee-drive-tile__preview',
          'employee-drive-tile__preview--image' => $url && $isImage,
          'employee-drive-tile__preview--pdf' => $url && $isPdf,
          'employee-drive-tile__preview--file' => $url && ! $isImage && ! $isPdf,
          'employee-drive-tile__preview--empty' => ! $url,
      ])
      @if ($url)
          target="_blank"
          rel="noopener noreferrer"
          title="Abrir"
      @else
          tabindex="-1"
          aria-hidden="true"
          onclick="return false;"
      @endif
  >
    @if ($url && $isImage)
        <img
            src="{{ $url }}"
            alt=""
            @class([
                'employee-drive-tile__image',
                'employee-drive-tile__image--contain' => $imageFit === 'contain',
            ])
            loading="lazy"
        />
    @elseif ($url && $isPdf)
        <span class="employee-drive-tile__type">pdf</span>
    @elseif ($url && ! $isImage)
        <x-filament::icon icon="heroicon-o-document" class="employee-drive-tile__icon" />
        @if ($extension)
            <span class="employee-drive-tile__type">{{ strtolower($extension) }}</span>
        @endif
    @elseif (! $url)
        <x-filament::icon icon="heroicon-o-arrow-up-tray" class="employee-drive-tile__icon" />
    @endif
  </a>
  </div>

  @if (($layout ?? 'full') !== 'thumb')
      <div class="employee-drive-tile__meta">
          <p class="employee-drive-tile__name" title="{{ $displayName }}">{{ $displayName }}</p>
          @if ($uploadedLabel)
              <p class="employee-drive-tile__date">{{ $uploadedLabel }}</p>
          @endif
      </div>
  @endif
</div>