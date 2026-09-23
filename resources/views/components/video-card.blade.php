@props(['item', 'class' => ''])

@php $progress = $item->relationLoaded('myProgress') ? $item->myProgress?->progress : null; @endphp

<a href="{{ route('videos.show', $item) }}" {{ $attributes->merge(['class' => 'video-card group block min-w-0 '.$class]) }}>
    <div class="relative aspect-video overflow-hidden rounded-md border border-border-subtle bg-surface-card transition group-hover:border-primary/60 group-hover:shadow-glow">
        <img src="{{ route('media.cover', $item) }}" alt="" loading="lazy" class="size-full object-cover transition duration-500 group-hover:scale-[1.03]">
        <span class="absolute inset-0 flex items-center justify-center opacity-0 transition group-hover:opacity-100">
            <span class="flex size-12 items-center justify-center rounded-full bg-primary text-black shadow-glow"><x-icon name="play" filled class="ml-0.5 size-5" /></span>
        </span>
        @if ($item->formattedDuration())
            <span class="type-label-sm absolute right-2 bottom-2 rounded-full bg-black/85 px-2 py-1 text-text-primary">{{ $item->formattedDuration() }}</span>
        @endif
        @if ($item->created_at?->gt(now()->subDays(7)))
            <span class="badge absolute top-2 left-2 bg-neon-cyan text-black">New</span>
        @endif
        @if ($progress)
            <span class="absolute inset-x-0 bottom-0 h-1 bg-black/60"><span class="block h-full bg-primary" style="width: {{ round($progress * 100) }}%"></span></span>
        @else
            <span class="scrubber absolute inset-x-0 bottom-0 h-0.5 bg-primary"></span>
        @endif
    </div>
    <div class="mt-3 min-w-0">
        <h3 class="truncate text-[15px] font-bold text-text-primary group-hover:text-primary">{{ $item->title }}</h3>
        <p class="mt-0.5 truncate text-[13px] leading-[18px] text-text-dim">
            {{ collect([$item->collection, $item->year, strtoupper($item->extension)])->filter()->join(' · ') }}
        </p>
    </div>
</a>
