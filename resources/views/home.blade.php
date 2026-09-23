<x-layouts.app>
    @if ($featured)
        {{-- Hero --}}
        <section class="relative -mx-4 -mt-6 overflow-hidden md:mx-0 md:mt-0 md:rounded-lg md:border md:border-border-subtle">
            <img src="{{ route('media.cover', $featured) }}" alt="" class="absolute inset-0 size-full object-cover opacity-60 blur-[1px]">
            <div class="absolute inset-0 bg-gradient-to-r from-surface-deep via-surface-deep/80 to-transparent"></div>
            <div class="scrim absolute inset-0"></div>
            <div class="relative flex min-h-[360px] flex-col justify-end gap-4 px-4 py-8 md:min-h-[440px] md:max-w-3xl md:px-10 md:py-12">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="badge bg-crimson text-white">Featured</span>
                    @if ($featured->collection)
                        <span class="chip pointer-events-none">{{ $featured->collection }}</span>
                    @endif
                    @if ($featured->formattedDuration())
                        <span class="type-label text-text-muted">{{ $featured->formattedDuration() }}</span>
                    @endif
                </div>
                <h1 class="type-hero line-clamp-3">{{ $featured->title }}</h1>
                @if ($featured->description)
                    <p class="line-clamp-2 max-w-xl text-lg leading-7 text-text-muted">{{ $featured->description }}</p>
                @endif
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('videos.show', $featured) }}" class="btn-primary"><x-icon name="play" filled class="size-4" /> Watch now</a>
                    <a href="{{ route('videos.index') }}" class="btn-secondary">Browse videos</a>
                </div>
            </div>
        </section>
    @else
        <section class="relative overflow-hidden rounded-lg border border-border-subtle bg-surface-deep px-6 py-12 md:px-10 md:py-16">
            <div class="absolute -top-24 -right-24 size-72 rounded-full bg-primary/10 blur-3xl"></div>
            <span class="type-label text-primary">Your home server, streaming</span>
            <h1 class="type-hero mt-3 max-w-3xl">Movies, music and books. All yours.</h1>
            <p class="mt-4 max-w-xl text-lg leading-7 text-text-muted">RUKRE streams the video, audio and eBooks stored on your home server, straight to your browser.</p>
        </section>
    @endif

    @if ($continue->isNotEmpty())
        <x-shelf title="Continue">
            @foreach ($continue as $item)
                <x-media-card :item="$item"
                    :class="$item->type === \App\Enums\MediaType::Video ? 'w-64 shrink-0 snap-start md:w-72' : 'w-36 shrink-0 snap-start md:w-44'" />
            @endforeach
        </x-shelf>
    @endif

    @if ($videos->isNotEmpty())
        <x-shelf title="Recently added videos" :href="route('videos.index')" :count="$counts['video'] ?? null">
            @foreach ($videos as $item)
                <x-video-card :item="$item" class="w-64 shrink-0 snap-start md:w-72" />
            @endforeach
        </x-shelf>
    @endif

    @if ($audio->isNotEmpty())
        <x-shelf title="Fresh audio" :href="route('audio.index')" :count="$counts['audio'] ?? null">
            @foreach ($audio as $item)
                <x-audio-card :item="$item" queue="queue-home-audio" :index="$loop->index" class="w-36 shrink-0 snap-start md:w-44" />
            @endforeach
        </x-shelf>
        <x-play-queue id="queue-home-audio" :items="$audio" />
    @endif

    @if ($books->isNotEmpty())
        <x-shelf title="New on the shelf" :href="route('books.index')" :count="$counts['book'] ?? null">
            @foreach ($books as $item)
                <x-book-card :item="$item" class="w-36 shrink-0 snap-start md:w-44" />
            @endforeach
        </x-shelf>
    @endif

    @if ($videos->isEmpty() && $audio->isEmpty() && $books->isEmpty())
        <x-empty-state title="Your library is empty" icon="server" class="mt-10">
            Point RUKRE at your media folders in <code class="text-primary">.env</code>, then run
            <code class="text-primary">php artisan rukre:scan</code> to index your videos, audio and books.
        </x-empty-state>
    @endif
</x-layouts.app>
