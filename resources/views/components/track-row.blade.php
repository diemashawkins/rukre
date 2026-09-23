@props(['item', 'queue', 'index', 'number' => null])

<div class="track-row group grid grid-cols-[2.5rem_3rem_1fr_auto] items-center gap-3 rounded-md px-2 py-2 transition hover:bg-surface-card md:grid-cols-[2.5rem_3rem_1fr_1fr_auto]" data-track-id="{{ $item->id }}">
    <button type="button" data-queue="{{ $queue }}" data-index="{{ $index }}" class="flex h-10 items-center justify-center" aria-label="Play {{ $item->title }}">
        <span class="track-index font-label text-sm font-semibold text-text-dim tabular-nums">{{ $number ?? $index + 1 }}</span>
        <span class="track-play text-primary"><x-icon name="play" filled class="size-5" /></span>
    </button>
    <img src="{{ route('media.cover', $item) }}" alt="" loading="lazy" class="size-12 rounded-sm border border-border-subtle object-cover">
    <button type="button" data-queue="{{ $queue }}" data-index="{{ $index }}" class="min-w-0 text-left">
        <span class="track-title block truncate text-[15px] font-bold text-text-primary">{{ $item->title }}</span>
        <span class="block truncate text-[13px] leading-[18px] text-text-dim">{{ $item->creator ?? 'Unknown artist' }}</span>
    </button>
    <a href="{{ route('audio.show', $item) }}" class="hidden min-w-0 truncate text-[13px] text-text-muted hover:text-primary md:block">{{ $item->album ?? $item->collection }}</a>
    <span class="type-label text-text-dim tabular-nums">{{ $item->formattedDuration() ?? strtoupper($item->extension) }}</span>
</div>
