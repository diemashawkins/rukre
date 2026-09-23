@props(['item', 'queue' => null, 'index' => 0, 'href' => null, 'title' => null, 'subtitle' => null, 'class' => ''])

<div {{ $attributes->merge(['class' => 'group relative min-w-0 '.$class]) }}>
    <a href="{{ $href ?? route('audio.show', $item) }}" class="block">
        <div class="relative aspect-square overflow-hidden rounded-md border border-border-subtle bg-surface-card transition group-hover:border-primary/60 group-hover:shadow-glow">
            <img src="{{ route('media.cover', $item) }}" alt="" loading="lazy" class="size-full object-cover transition duration-500 group-hover:scale-[1.03]">
        </div>
        <h3 class="mt-3 truncate text-[15px] font-bold text-text-primary group-hover:text-primary">{{ $title ?? $item->title }}</h3>
        <p class="mt-0.5 truncate text-[13px] leading-[18px] text-text-dim">{{ $subtitle ?? ($item->creator ?? $item->album ?? $item->collection ?? 'Unknown artist') }}</p>
    </a>
    @if ($queue)
        <button type="button" data-queue="{{ $queue }}" data-index="{{ $index }}" aria-label="Play {{ $title ?? $item->title }}"
            class="absolute right-2 bottom-[4.25rem] flex size-11 translate-y-2 items-center justify-center rounded-full bg-primary text-black opacity-0 shadow-glow transition group-hover:translate-y-0 group-hover:opacity-100 focus-visible:translate-y-0 focus-visible:opacity-100">
            <x-icon name="play" filled class="ml-0.5 size-5" />
        </button>
    @endif
</div>
