import { formatTime, saveProgress } from './progress';

const STORAGE_KEY = 'rukre.player';

/**
 * The persistent audio bar. It lives outside the swapped page content
 * (hx-preserve), so music keeps playing while you browse.
 */
export function initMiniPlayer() {
    const root = document.querySelector('[data-mini-player]');

    if (!root || root.dataset.booted) {
        return;
    }

    root.dataset.booted = '1';

    const $ = (selector) => root.querySelector(selector);
    const audio = $('[data-player-audio]');
    const seek = $('[data-player-seek]');
    const volume = $('[data-player-volume]');
    const time = $('[data-player-time]');

    let queue = [];
    let index = -1;
    let lastSaved = 0;
    let lastStored = 0;

    const current = () => queue[index];

    function store() {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify({ queue, index, time: audio.currentTime, volume: audio.volume }));
        } catch {
            // Storage can be unavailable (private mode); the player still works.
        }
    }

    function restore() {
        try {
            const saved = JSON.parse(localStorage.getItem(STORAGE_KEY) || 'null');

            if (saved && Array.isArray(saved.queue) && saved.queue[saved.index]) {
                audio.volume = saved.volume ?? 1;
                volume.value = String(Math.round(audio.volume * 100));
                load(saved.queue, saved.index, { autoplay: false, startAt: saved.time || 0 });
            }
        } catch {
            // Ignore broken saved state.
        }
    }

    function persistProgress(options = {}) {
        const track = current();

        if (!track || !audio.duration || !Number.isFinite(audio.duration)) {
            return;
        }

        saveProgress(track.progress, {
            position: audio.currentTime,
            progress: audio.currentTime / audio.duration,
            completed: audio.ended,
        }, options);
    }

    function render() {
        const track = current();

        if (!track) {
            return;
        }

        root.classList.remove('hidden');
        $('[data-player-cover]').src = track.cover;
        $('[data-player-title]').textContent = track.title;
        $('[data-player-creator]').textContent = track.creator || '';
        $('[data-player-link]').href = track.url;
        highlight();

        if ('mediaSession' in navigator) {
            navigator.mediaSession.metadata = new MediaMetadata({
                title: track.title,
                artist: track.creator || '',
                artwork: [{ src: track.cover }],
            });
        }
    }

    function renderPlaying() {
        const playing = !audio.paused;
        $('[data-icon-play]').classList.toggle('hidden', playing);
        $('[data-icon-pause]').classList.toggle('hidden', !playing);
        $('[data-player-toggle]').setAttribute('aria-label', playing ? 'Pause' : 'Play');
    }

    function renderTime() {
        const fill = audio.duration ? (audio.currentTime / audio.duration) * 100 : 0;
        seek.value = String(Math.round(fill * 10));
        seek.style.setProperty('--fill', `${fill}%`);
        time.textContent = `${formatTime(audio.currentTime)} / ${formatTime(audio.duration)}`;
    }

    function highlight() {
        const track = current();

        document.querySelectorAll('[data-track-id]').forEach((row) => {
            row.classList.toggle('is-playing', Boolean(track) && row.dataset.trackId === String(track.id));
        });
    }

    function load(tracks, at, { autoplay = true, startAt = 0 } = {}) {
        if (current()) {
            persistProgress();
        }

        queue = tracks;
        index = Math.max(0, Math.min(at, tracks.length - 1));
        const track = current();

        audio.src = track.src;
        lastSaved = 0;

        if (startAt > 0) {
            audio.addEventListener('loadedmetadata', () => { audio.currentTime = startAt; }, { once: true });
        }

        render();
        renderTime();

        if (autoplay) {
            audio.play().catch(() => renderPlaying());
        }

        store();
    }

    function step(delta) {
        if (!queue.length) {
            return;
        }

        if (delta < 0 && audio.currentTime > 3) {
            audio.currentTime = 0;

            return;
        }

        const next = index + delta;

        if (next >= 0 && next < queue.length) {
            load(queue, next);
        }
    }

    audio.addEventListener('play', renderPlaying);
    audio.addEventListener('pause', () => { renderPlaying(); persistProgress(); store(); });
    audio.addEventListener('loadedmetadata', renderTime);
    audio.addEventListener('timeupdate', () => {
        renderTime();

        if (Math.abs(audio.currentTime - lastSaved) >= 15) {
            lastSaved = audio.currentTime;
            persistProgress();
        }

        if (Math.abs(audio.currentTime - lastStored) >= 5) {
            lastStored = audio.currentTime;
            store();
        }
    });
    audio.addEventListener('ended', () => {
        persistProgress();

        if (index < queue.length - 1) {
            load(queue, index + 1);
        } else {
            renderPlaying();
        }
    });

    $('[data-player-toggle]').addEventListener('click', () => (audio.paused ? audio.play() : audio.pause()));
    $('[data-player-prev]').addEventListener('click', () => step(-1));
    $('[data-player-next]').addEventListener('click', () => step(1));
    $('[data-player-close]').addEventListener('click', () => {
        audio.pause();
        persistProgress();
        root.classList.add('hidden');
        queue = [];
        index = -1;
        highlight();

        try {
            localStorage.removeItem(STORAGE_KEY);
        } catch {
            // Nothing to clean up.
        }
    });
    $('[data-player-mute]').addEventListener('click', () => { audio.muted = !audio.muted; });

    seek.addEventListener('input', () => {
        if (audio.duration) {
            audio.currentTime = (Number(seek.value) / 1000) * audio.duration;
        }
    });

    volume.addEventListener('input', () => {
        audio.volume = Number(volume.value) / 100;
        volume.style.setProperty('--fill', `${volume.value}%`);
    });
    volume.style.setProperty('--fill', '100%');

    if ('mediaSession' in navigator) {
        navigator.mediaSession.setActionHandler('play', () => audio.play());
        navigator.mediaSession.setActionHandler('pause', () => audio.pause());
        navigator.mediaSession.setActionHandler('previoustrack', () => step(-1));
        navigator.mediaSession.setActionHandler('nexttrack', () => step(1));
    }

    // Any [data-queue] button plays from the JSON queue it points at.
    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-queue]');

        if (!button) {
            return;
        }

        const source = document.getElementById(button.dataset.queue);

        if (!source) {
            return;
        }

        event.preventDefault();
        let tracks = JSON.parse(source.textContent || '[]');
        let at = Number(button.dataset.index || 0);

        if (!tracks.length) {
            return;
        }

        if ('shuffle' in button.dataset) {
            tracks = tracks.map((track) => [Math.random(), track]).sort((a, b) => a[0] - b[0]).map(([, track]) => track);
            at = 0;
        }

        const track = current();

        if (track && tracks[at]?.id === track.id && !('shuffle' in button.dataset)) {
            audio.paused ? audio.play() : audio.pause();

            return;
        }

        load(tracks, at);
    });

    // A video starting on the page pauses the music.
    document.addEventListener('play', (event) => {
        if (event.target instanceof HTMLVideoElement) {
            audio.pause();
        }
    }, true);

    document.addEventListener('htmx:afterSettle', highlight);
    window.addEventListener('pagehide', () => { persistProgress({ beacon: true }); store(); });

    restore();
}
