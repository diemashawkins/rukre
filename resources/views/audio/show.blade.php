@php $index = $tracks->search(fn ($track) => $track->is($item)); @endphp

<x-layouts.app :title="$item->title">
    <section class="flex flex-col gap-6 md:flex-row md:items-end md:gap-10">
        <img src="{{ route('media.cover', $item) }}" alt="" class="aspect-square w-56 rounded-md border border-border-subtle object-cover shadow-flyout md:w-72">
        <div class="min-w-0">
            <span class="type-label text-primary">{{ $albumKey !== '' ? 'Album' : 'Track' }}</span>
            <h1 class="type-hero mt-2 line-clamp-3">{{ $item->title }}</h1>
            <p class="mt-3 text-lg text-text-muted">
                {{ collect([$item->creator, $albumKey !== '' ? $albumKey : null, $item->year])->filter()->join(' · ') }}
            </p>
            <div class="mt-6 flex flex-wrap gap-3">
                <button type="button" data-queue="queue-album" data-index="{{ $index === false ? 0 : $index }}" class="btn-primary"><x-icon name="play" filled class="size-4" /> Play</button>
                <button type="button" data-queue="queue-album" data-shuffle class="btn-secondary"><x-icon name="shuffle" class="size-4" /> Shuffle</button>
                <a href="{{ route('media.download', $item) }}" hx-boost="false" class="btn-secondary"><x-icon name="download" class="size-4" /> Download</a>
            </div>
        </div>
    </section>

    @if ($item->description)
        <p class="mt-8 max-w-3xl text-text-muted">{{ $item->description }}</p>
    @endif

    <section class="mt-10">
        <h2 class="type-title mb-4">{{ $albumKey !== '' ? 'Tracklist' : 'Track' }}</h2>
        <div class="flex flex-col">
            @foreach ($tracks as $track)
                <x-track-row :item="$track" queue="queue-album" :index="$loop->index" :number="$track->track_number ?? $loop->iteration" />
            @endforeach
        </div>
        <x-play-queue id="queue-album" :items="$tracks" />
    </section>
</x-layouts.app>
