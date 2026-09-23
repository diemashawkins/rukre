import { onLeave, saveProgress } from './progress';

export function initVideoPlayer(video) {
    const url = video.dataset.progressUrl;
    const resume = parseFloat(video.dataset.resume || '0');
    let lastSaved = 0;

    const save = (options = {}) => {
        if (!video.duration || !Number.isFinite(video.duration)) {
            return;
        }

        saveProgress(url, {
            position: video.currentTime,
            progress: video.currentTime / video.duration,
            completed: video.ended,
        }, options);
    };

    video.addEventListener('loadedmetadata', () => {
        if (resume > 10 && resume < video.duration - 15) {
            video.currentTime = resume;
        }
    }, { once: true });

    video.addEventListener('timeupdate', () => {
        if (Math.abs(video.currentTime - lastSaved) >= 10) {
            lastSaved = video.currentTime;
            save();
        }
    });

    video.addEventListener('pause', () => save());

    video.addEventListener('ended', () => {
        save();
        const next = document.querySelector('[data-next-video]');

        if (next) {
            setTimeout(() => video.isConnected && next.click(), 1500);
        }
    });

    onLeave(video, () => save({ beacon: true }));
}
