@php
    $sections = [
        'video' => ['title' => 'Videos', 'grid' => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4'],
        'audio' => ['title' => 'Audio', 'grid' => ''],
        'book' => ['title' => 'Books', 'grid' => 'grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6'],
    ];
    $total = $results->sum->count();
@endphp

<x-layouts.app title="Search">
    <span class="type-label text-primary">Search</span>
    <h1 class="type-headline mt-1">{{ $term === '' ? 'Find something to play' : '“'.$term.'”' }}</h1>
    @if ($term !== '')
        <p class="mt-2 text-text-muted">{{ $total }} {{ str('result')->plural($total) }}</p>
    @endif

    @if ($term !== '' && $total === 0)
        <x-empty-state title="No matches" icon="search" class="mt-8">Try a different title, artist, author or folder name.</x-empty-state>
    @endif

    @foreach ($sections as $type => $section)
        @continue($results[$type]->isEmpty())
        <section class="mt-10">
            <h2 class="type-title mb-4">{{ $section['title'] }}</h2>
            @if ($type === 'audio')
                <div class="flex flex-col">
                    @foreach ($results[$type] as $track)
                        <x-track-row :item="$track" queue="queue-search" :index="$loop->index" />
                    @endforeach
                </div>
                <x-play-queue id="queue-search" :items="$results[$type]" />
            @else
                <div class="grid gap-x-4 gap-y-8 {{ $section['grid'] }}">
                    @foreach ($results[$type] as $item)
                        <x-media-card :item="$item" />
                    @endforeach
                </div>
            @endif
        </section>
    @endforeach
</x-layouts.app>
