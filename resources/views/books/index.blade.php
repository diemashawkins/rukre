<x-layouts.app title="Books">
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <span class="type-label text-primary">Library</span>
            <h1 class="type-headline mt-1">Books & Comics</h1>
        </div>
        <div class="flex gap-2">
            <a href="{{ request()->fullUrlWithQuery(['sort' => null, 'page' => null]) }}" @class(['chip', 'chip-active' => $sort === 'recent'])>Recent</a>
            <a href="{{ request()->fullUrlWithQuery(['sort' => 'title', 'page' => null]) }}" @class(['chip', 'chip-active' => $sort === 'title'])>A–Z</a>
        </div>
    </div>

    <x-chip-bar>
        <a href="{{ request()->fullUrlWithQuery(['format' => null, 'page' => null]) }}" @class(['chip', 'chip-active' => $format === null])>All formats</a>
        @foreach ($formats as $key => $label)
            <a href="{{ request()->fullUrlWithQuery(['format' => $key, 'page' => null]) }}" @class(['chip', 'chip-active' => $format === $key])>{{ $label }}</a>
        @endforeach
        @foreach ($collections as $name => $total)
            <a href="{{ request()->fullUrlWithQuery(['collection' => $collection === $name ? null : $name, 'page' => null]) }}" @class(['chip', 'chip-active' => $collection === $name])>
                <x-icon name="folder" class="size-3.5" /> {{ $name }} <span class="opacity-60">{{ $total }}</span>
            </a>
        @endforeach
    </x-chip-bar>

    @if ($books->isEmpty())
        <x-empty-state title="No books found" icon="book">
            Put PDF, EPUB or CBZ files into your books folder on the home server and run <code class="text-primary">php artisan rukre:scan</code>.
        </x-empty-state>
    @else
        <div class="grid grid-cols-2 gap-x-4 gap-y-8 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 2xl:grid-cols-7">
            @foreach ($books as $item)
                <x-book-card :item="$item" />
            @endforeach
        </div>
        <x-pagination :paginator="$books" />
    @endif
</x-layouts.app>
