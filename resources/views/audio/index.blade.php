<x-layouts.app title="Audio">
    <div class="mb-6">
        <span class="type-label text-primary">Library</span>
        <h1 class="type-headline mt-1">{{ $album !== null ? ($album === '' ? 'Loose tracks' : $album) : 'Music & Audio' }}</h1>
    </div>

    @if ($album === null && $albums->isNotEmpty())
        <x-shelf title="Albums & folders" class="!mt-0">
            @foreach ($albums as $row)
                @php $cover = $albumCovers->get($row->cover_id); @endphp
                @if ($cover)
                    <x-audio-card :item="$cover" :href="route('audio.index', ['album' => $row->album_key])"
                        :title="$row->album_key === '' ? 'Loose tracks' : $row->album_key"
                        :subtitle="trim(($row->creator ? $row->creator.' · ' : '').$row->tracks.' '.str('track')->plural($row->tracks))"
                        class="w-36 shrink-0 snap-start md:w-44" />
                @endif
            @endforeach
        </x-shelf>
    @endif

    @if ($album !== null)
        <div class="mb-4 flex flex-wrap gap-3">
            <button type="button" data-queue="queue-tracks" data-index="0" class="btn-primary"><x-icon name="play" filled class="size-4" /> Play all</button>
            <button type="button" data-queue="queue-tracks" data-shuffle class="btn-secondary"><x-icon name="shuffle" class="size-4" /> Shuffle</button>
            <a href="{{ route('audio.index') }}" class="btn-secondary"><x-icon name="left" class="size-4" /> All audio</a>
        </div>
    @endif

    <section class="{{ $album === null ? 'mt-10' : '' }}">
        @if ($album === null)
            <h2 class="type-title mb-4">Recently added</h2>
        @endif

        @if ($tracks->isEmpty())
            <x-empty-state title="No audio found" icon="music">
                Drop music, podcasts or audiobooks into your audio folder on the home server and run <code class="text-primary">php artisan rukre:scan</code>.
            </x-empty-state>
        @else
            <div class="flex flex-col">
                @foreach ($tracks as $track)
                    <x-track-row :item="$track" queue="queue-tracks" :index="$loop->index" :number="$album !== null ? ($track->track_number ?? $loop->iteration) : $tracks->firstItem() + $loop->index" />
                @endforeach
            </div>
            <x-play-queue id="queue-tracks" :items="$tracks->getCollection()" />
            <x-pagination :paginator="$tracks" />
        @endif
    </section>
</x-layouts.app>
