@props(['title', 'href' => null, 'count' => null])

<section {{ $attributes->merge(['class' => 'mt-10 md:mt-12']) }}>
    <div class="mb-4 flex items-end justify-between gap-4">
        <h2 class="type-title">
            {{ $title }}
            @if ($count)
                <span class="type-label ml-2 align-middle text-text-dim">{{ number_format($count) }}</span>
            @endif
        </h2>
        @if ($href)
            <a href="{{ $href }}" class="type-label flex shrink-0 items-center gap-1 text-text-muted hover:text-primary">See all <x-icon name="right" class="size-4" /></a>
        @endif
    </div>
    <div class="shelf">
        {{ $slot }}
    </div>
</section>
