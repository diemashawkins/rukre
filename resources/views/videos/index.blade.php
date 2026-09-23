<x-layouts.app title="Videos">
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <span class="type-label text-primary">Library</span>
            <h1 class="type-headline mt-1">Videos</h1>
        </div>
        <div class="flex gap-2">
            <a href="{{ request()->fullUrlWithQuery(['sort' => null, 'page' => null]) }}" @class(['chip', 'chip-active' => $sort === 'recent'])>Recent</a>
            <a href="{{ request()->fullUrlWithQuery(['sort' => 'title', 'page' => null]) }}" @class(['chip', 'chip-active' => $sort === 'title'])>A–Z</a>
        </div>
    </div>

    @if ($collections)
        <x-chip-bar>
            <a href="{{ route('videos.index', ['sort' => $sort === 'title' ? 'title' : null]) }}" @class(['chip', 'chip-active' => $collection === null])>All</a>
            @foreach ($collections as $name => $total)
                <a href="{{ route('videos.index', ['collection' => $name, 'sort' => $sort === 'title' ? 'title' : null]) }}" @class(['chip', 'chip-active' => $collection === $name])>
                    {{ $name }} <span class="opacity-60">{{ $total }}</span>
                </a>
            @endforeach
        </x-chip-bar>
    @endif

    @if ($videos->isEmpty())
        <x-empty-state title="No videos found" icon="film">
            Add movies or shows to your video folder on the home server and run <code class="text-primary">php artisan rukre:scan</code>.
        </x-empty-state>
    @else
        <div class="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 min-[1800px]:grid-cols-5">
            @foreach ($videos as $item)
                <x-video-card :item="$item" />
            @endforeach
        </div>
        <x-pagination :paginator="$videos" />
    @endif
</x-layouts.app>
