@php
    $webFriendly = in_array($item->extension, ['mp4', 'm4v', 'webm', 'mov', 'ogv'], true);
    $progress = $item->myProgress;
    $resume = $progress && ! $progress->completed ? $progress->position : 0;
    $index = $upNext->search(fn ($video) => $video->is($item));
    $next = $index === false ? $upNext->first(fn ($video) => ! $video->is($item)) : $upNext->get($index + 1);
@endphp

<x-layouts.app :title="$item->title">
    <div class="grid gap-8 xl:grid-cols-[minmax(0,1fr)_380px]">
        <div class="min-w-0">
            <div class="-mx-4 overflow-hidden bg-black md:mx-0 md:rounded-lg md:border md:border-border-subtle">
                <video
                    data-video-player
                    data-progress-url="{{ route('media.progress', $item) }}"
                    data-resume="{{ $resume }}"
                    class="aspect-video w-full bg-black"
                    src="{{ route('media.stream', $item) }}"
                    poster="{{ route('media.cover', $item) }}"
                    controls playsinline preload="metadata"
                ></video>
            </div>

            <div class="mt-6 flex flex-wrap items-start justify-between gap-4">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($item->collection)
                            <a href="{{ route('videos.index', ['collection' => $item->collection]) }}" class="chip">{{ $item->collection }}</a>
                        @endif
                        <span class="type-label text-text-dim">{{ collect([$item->year, $item->formattedDuration(), strtoupper($item->extension), $item->formattedSize()])->filter()->join(' · ') }}</span>
                    </div>
                    <h1 class="type-headline mt-3">{{ $item->title }}</h1>
                    @if ($item->creator)
                        <p class="mt-1 text-text-muted">{{ $item->creator }}</p>
                    @endif
                </div>
                <a href="{{ route('media.download', $item) }}" hx-boost="false" class="btn-secondary"><x-icon name="download" class="size-4" /> Download</a>
            </div>

            @unless ($webFriendly)
                <div class="card mt-6 flex gap-3 border-crimson/40 p-4 text-text-muted">
                    <x-icon name="alert" class="size-5 text-crimson" />
                    <p><strong class="text-text-primary">{{ strtoupper($item->extension) }} files may not play in every browser.</strong>
                        If playback doesn't start, download the file or open it in VLC with this link: <span class="break-all text-primary">{{ route('media.stream', $item) }}</span></p>
                </div>
            @endunless

            @if ($item->description)
                <p class="mt-6 max-w-3xl text-lg leading-7 text-text-muted">{{ $item->description }}</p>
            @endif
        </div>

        @if ($upNext->count() > 1 || ($upNext->isNotEmpty() && ! $upNext->first()->is($item)))
            <aside>
                <h2 class="type-subtitle mb-3">{{ $item->collection ? 'Episodes' : 'More videos' }}</h2>
                @if ($next)
                    <a href="{{ route('videos.show', $next) }}" data-next-video class="sr-only">Next: {{ $next->title }}</a>
                @endif
                <ol class="flex flex-col gap-1">
                    @foreach ($upNext as $video)
                        @php $current = $video->is($item); @endphp
                        <li>
                            <a href="{{ route('videos.show', $video) }}" @class([
                                'track-row grid grid-cols-[2rem_6rem_1fr] items-center gap-3 rounded-md p-2 transition hover:bg-surface-card',
                                'is-playing' => $current,
                            ]) @if ($current) aria-current="true" @endif>
                                <span class="flex justify-center">
                                    <span class="track-index font-label text-sm font-semibold text-text-dim tabular-nums">{{ $video->track_number ?? $loop->iteration }}</span>
                                    <span class="track-play text-primary"><x-icon name="play" filled class="size-4" /></span>
                                </span>
                                <img src="{{ route('media.cover', $video) }}" alt="" loading="lazy" class="aspect-video w-24 rounded-sm border border-border-subtle object-cover">
                                <span class="min-w-0">
                                    <span class="track-title line-clamp-2 text-sm font-bold text-text-primary">{{ $video->title }}</span>
                                    <span class="type-label-sm mt-1 block text-text-dim">{{ $video->formattedDuration() }}</span>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ol>
            </aside>
        @endif
    </div>
</x-layouts.app>
