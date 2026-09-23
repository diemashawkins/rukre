/**
 * Save playback / reading progress for the signed-in user.
 * Uses sendBeacon when the page is going away so the last position isn't lost.
 */
export function saveProgress(url, { position, progress, location = null, completed = false }, { beacon = false } = {}) {
    if (!url || !Number.isFinite(position) || !Number.isFinite(progress)) {
        return;
    }

    const token = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const body = new FormData();
    body.append('_token', token);
    body.append('position', String(Math.max(0, position)));
    body.append('progress', String(Math.min(1, Math.max(0, progress))));
    body.append('completed', completed ? '1' : '0');

    if (location) {
        body.append('location', location);
    }

    if (beacon && navigator.sendBeacon) {
        navigator.sendBeacon(url, body);

        return;
    }

    fetch(url, { method: 'POST', body, credentials: 'same-origin', keepalive: true, headers: { Accept: 'application/json' } }).catch(() => {});
}

/**
 * Run a callback when the current page is swapped out or closed.
 */
export function onLeave(element, callback) {
    const remove = () => {
        document.removeEventListener('htmx:beforeRequest', handler);
        window.removeEventListener('pagehide', handler);
    };

    function handler() {
        if (element.isConnected) {
            callback();
        } else {
            remove();
        }
    }

    document.addEventListener('htmx:beforeRequest', handler);
    window.addEventListener('pagehide', handler);

    return remove;
}

export function formatTime(seconds) {
    if (!Number.isFinite(seconds) || seconds < 0) {
        return '0:00';
    }

    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = Math.floor(seconds % 60).toString().padStart(2, '0');

    return h > 0 ? `${h}:${m.toString().padStart(2, '0')}:${s}` : `${m}:${s}`;
}
