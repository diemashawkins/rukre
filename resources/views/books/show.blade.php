@php $progress = $item->myProgress; @endphp

<x-layouts.app :title="$item->title">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div class="flex min-w-0 items-center gap-3">
            <a href="{{ route('books.index') }}" class="btn-icon" aria-label="Back to books"><x-icon name="left" /></a>
            <div class="min-w-0">
                <h1 class="type-subtitle truncate md:text-2xl md:leading-8">{{ $item->title }}</h1>
                <p class="truncate text-[13px] text-text-dim">{{ collect([$item->creator, $item->year, strtoupper($item->extension), $item->formattedSize()])->filter()->join(' · ') }}</p>
            </div>
        </div>
        <a href="{{ route('media.download', $item) }}" hx-boost="false" class="btn-secondary"><x-icon name="download" class="size-4" /> Download</a>
    </div>

    @switch($reader)
        @case('epub')
            <div data-epub-reader
                 data-src="{{ route('media.stream', $item) }}"
                 data-progress-url="{{ route('media.progress', $item) }}"
                 data-location="{{ $progress?->location }}"
                 data-book-id="{{ $item->id }}"
                 class="card overflow-hidden">
                <div class="flex flex-wrap items-center gap-2 border-b border-border-subtle p-2 md:px-4">
                    <select data-epub-toc class="input h-9 max-w-[16rem] flex-1 rounded-full px-3 text-sm" aria-label="Chapters">
                        <option value="">Chapters</option>
                    </select>
                    <div class="ml-auto flex items-center gap-2">
                        <button type="button" data-epub-font class="chip" title="Switch between serif and sans-serif"><x-icon name="type" class="size-3.5" /> <span data-epub-font-label>Serif</span></button>
                        <button type="button" data-epub-smaller class="btn-icon size-9" aria-label="Smaller text"><x-icon name="minus" class="size-4" /></button>
                        <button type="button" data-epub-larger class="btn-icon size-9" aria-label="Larger text"><x-icon name="plus" class="size-4" /></button>
                    </div>
                </div>
                <div class="relative">
                    <div data-epub-viewer class="h-[calc(100dvh-27rem)] min-h-[420px] bg-canvas px-2 md:px-10 lg:h-[calc(100dvh-23rem)]"></div>
                    <div data-epub-loading class="absolute inset-0 flex items-center justify-center text-text-dim"><span class="type-label animate-pulse">Opening book…</span></div>
                    <button type="button" data-epub-prev class="btn-icon absolute top-1/2 left-2 -translate-y-1/2" aria-label="Previous page"><x-icon name="left" /></button>
                    <button type="button" data-epub-next class="btn-icon absolute top-1/2 right-2 -translate-y-1/2" aria-label="Next page"><x-icon name="right" /></button>
                </div>
                <div class="flex items-center gap-3 border-t border-border-subtle px-4 py-2">
                    <div class="h-1 flex-1 overflow-hidden rounded-full bg-border-subtle"><div data-epub-bar class="h-full bg-primary transition-[width]" style="width: {{ round(($progress?->progress ?? 0) * 100) }}%"></div></div>
                    <span data-epub-percent class="type-label text-text-dim tabular-nums">{{ round(($progress?->progress ?? 0) * 100) }}%</span>
                </div>
            </div>
            @break

        @case('pdf')
            <div data-pdf-reader data-progress-url="{{ route('media.progress', $item) }}" class="card overflow-hidden">
                <iframe src="{{ route('media.stream', $item) }}#view=FitH" title="{{ $item->title }}" class="h-[calc(100dvh-22rem)] min-h-[480px] w-full bg-white lg:h-[calc(100dvh-18rem)]"></iframe>
            </div>
            <p class="mt-3 text-[13px] text-text-dim">Using your browser's built-in PDF viewer. If nothing shows up, use Download.</p>
            @break

        @case('comic')
            <div data-comic-reader data-progress-url="{{ route('media.progress', $item) }}" data-page-count="{{ $pageCount }}" data-resume="{{ (int) ($progress?->position ?? 0) }}" class="-mx-4 flex flex-col items-center bg-surface-deep md:mx-0 md:rounded-lg">
                @for ($page = 0; $page < $pageCount; $page++)
                    <img src="{{ route('books.page', [$item, $page]) }}" alt="Page {{ $page + 1 }}" loading="lazy" data-page="{{ $page }}" class="block w-full max-w-3xl min-h-40">
                @endfor
            </div>
            @break

        @case('text')
            <article data-text-reader data-src="{{ route('media.stream', $item) }}" data-progress-url="{{ route('media.progress', $item) }}" data-resume="{{ $progress?->progress ?? 0 }}"
                class="reader-serif card mx-auto max-w-3xl px-6 py-8 text-lg leading-[1.75] whitespace-pre-wrap text-text-body md:px-12">
                <span class="type-label animate-pulse text-text-dim">Loading…</span>
            </article>
            @break

        @default
            <div class="card flex flex-col items-center gap-6 p-8 text-center md:flex-row md:text-left">
                <img src="{{ route('media.cover', $item) }}" alt="" class="aspect-[3/4] w-40 rounded-md border border-border-subtle object-cover">
                <div>
                    <h2 class="type-title">Read {{ strtoupper($item->extension) }} in your favourite app</h2>
                    <p class="mt-2 max-w-lg text-text-muted">Browsers can't open {{ strtoupper($item->extension) }} files directly. Download it and open it in an eReader app such as Calibre, KOReader or Apple Books.</p>
                    <a href="{{ route('media.download', $item) }}" hx-boost="false" class="btn-primary mt-6"><x-icon name="download" class="size-4" /> Download {{ $item->formattedSize() }}</a>
                </div>
            </div>
    @endswitch

    @if ($item->description)
        <section class="mt-10 max-w-3xl">
            <h2 class="type-subtitle mb-2">About this book</h2>
            <p class="text-text-muted">{{ $item->description }}</p>
        </section>
    @endif

    @if ($related->isNotEmpty())
        <x-shelf :title="'More from '.$item->collection" :href="route('books.index', ['collection' => $item->collection])">
            @foreach ($related as $book)
                <x-book-card :item="$book" class="w-36 shrink-0 snap-start md:w-44" />
            @endforeach
        </x-shelf>
    @endif
</x-layouts.app>
