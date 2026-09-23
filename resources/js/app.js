import htmx from 'htmx.org';
import { initMiniPlayer } from './modules/mini-player';
import { initVideoPlayer } from './modules/video-player';
import { initComicReader, initPdfReader, initTextReader } from './modules/readers';

window.htmx = htmx;

htmx.config.allowEval = false;
htmx.config.scrollBehavior = 'instant';
htmx.config.historyCacheSize = 0;
htmx.config.refreshOnHistoryMiss = false;

const components = {
    'data-video-player': initVideoPlayer,
    'data-epub-reader': (element) => import('./modules/epub-reader').then(({ initEpubReader }) => initEpubReader(element)),
    'data-pdf-reader': initPdfReader,
    'data-comic-reader': initComicReader,
    'data-text-reader': initTextReader,
};

function boot(root) {
    for (const [attribute, init] of Object.entries(components)) {
        const matches = root.matches?.(`[${attribute}]`) ? [root] : [...root.querySelectorAll(`[${attribute}]`)];

        for (const element of matches) {
            if (element.dataset.booted) {
                continue;
            }

            element.dataset.booted = '1';
            init(element);
        }
    }
}

initMiniPlayer();
htmx.onLoad(boot);
