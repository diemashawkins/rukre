{{-- Persistent Spotify-style audio bar. hx-preserve keeps it (and the <audio>) alive across page changes. --}}
<div id="mini-player" hx-preserve="true" data-mini-player class="fixed inset-x-0 bottom-16 z-50 hidden lg:bottom-0 lg:left-60" aria-label="Audio player">
    <div class="glass relative border-t border-border-subtle">
        <div class="pointer-events-none absolute inset-x-0 -top-px h-px bg-gradient-to-r from-primary/40 to-transparent"></div>
        <input type="range" min="0" max="1000" value="0" step="1" data-player-seek aria-label="Seek" class="player-range absolute inset-x-0 -top-[2px] w-full">

        <div class="flex h-[68px] items-center gap-3 px-3 md:px-6">
            <a data-player-link href="#" class="flex min-w-0 flex-1 items-center gap-3 md:w-1/3 md:flex-none">
                <img data-player-cover src="" alt="" class="size-11 shrink-0 rounded-sm bg-surface-card object-cover">
                <span class="min-w-0">
                    <span data-player-title class="block truncate text-sm font-bold text-text-primary"></span>
                    <span data-player-creator class="block truncate text-[13px] leading-[18px] text-text-dim"></span>
                </span>
            </a>

            <div class="flex items-center gap-1 md:flex-1 md:justify-center md:gap-3">
                <button type="button" data-player-prev class="btn-icon hidden bg-transparent md:inline-flex" aria-label="Previous"><x-icon name="prev" filled class="size-4" /></button>
                <button type="button" data-player-toggle class="flex size-11 items-center justify-center rounded-full bg-primary text-black transition hover:scale-105 hover:bg-primary-hover" aria-label="Play">
                    <x-icon name="play" filled class="size-5" data-icon-play />
                    <x-icon name="pause" filled class="hidden size-5" data-icon-pause />
                </button>
                <button type="button" data-player-next class="btn-icon bg-transparent" aria-label="Next"><x-icon name="next" filled class="size-4" /></button>
            </div>

            <div class="hidden items-center justify-end gap-3 md:flex md:w-1/3">
                <span data-player-time class="type-label text-text-dim tabular-nums">0:00 / 0:00</span>
                <button type="button" data-player-mute class="btn-icon bg-transparent" aria-label="Mute"><x-icon name="volume" class="size-4" /></button>
                <input type="range" min="0" max="100" value="100" data-player-volume aria-label="Volume" class="player-range w-24 rounded-full">
                <button type="button" data-player-close class="btn-icon bg-transparent" aria-label="Close player"><x-icon name="close" class="size-4" /></button>
            </div>
        </div>
    </div>
    <audio data-player-audio preload="metadata"></audio>
</div>
