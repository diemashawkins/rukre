@props(['item', 'class' => ''])

@php $progress = $item->relationLoaded('myProgress') ? $item->myProgress?->progress : null; @endphp

<a href="{{ route('books.show', $item) }}" {{ $attributes->merge(['class' => 'group block min-w-0 '.$class]) }}>
    <div class="relative aspect-[3/4] overflow-hidden rounded-md border border-border-subtle bg-surface-card transition group-hover:border-primary/60 group-hover:shadow-glow">
        <img src="{{ route('media.cover', $item) }}" alt="" loading="lazy" class="size-full object-cover transition duration-500 group-hover:scale-[1.03]">
        <div class="scrim absolute inset-0"></div>
        <span @class([
            'badge absolute top-2 left-2 rounded-full px-2 py-1',
            'bg-primary text-black' => $item->readerKind() !== null,
            'bg-surface-elevated text-text-muted' => $item->readerKind() === null,
        ])>{{ $item->extension === 'cbz' ? 'Comic' : strtoupper($item->extension) }}</span>
        @if ($progress)
            <span class="absolute inset-x-2 bottom-2 h-1 overflow-hidden rounded-full bg-white/15"><span class="block h-full rounded-full bg-primary" style="width: {{ max(3, round($progress * 100)) }}%"></span></span>
        @endif
    </div>
    <h3 class="mt-3 line-clamp-2 text-[15px] font-bold text-text-primary group-hover:text-primary">{{ $item->title }}</h3>
    <p class="mt-0.5 truncate text-[13px] leading-[18px] text-text-dim">{{ $item->creator ?? $item->collection ?? $item->formattedSize() }}</p>
</a>
