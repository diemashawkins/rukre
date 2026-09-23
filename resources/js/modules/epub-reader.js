import ePub from 'epubjs';
import { onLeave, saveProgress } from './progress';

const PREFS_KEY = 'rukre.reader';

const FONTS = {
    serif: "'Literata', 'Source Serif 4', Georgia, serif",
    sans: "'Plus Jakarta Sans', system-ui, sans-serif",
};

function loadPrefs() {
    try {
        return { font: 'serif', size: 110, ...JSON.parse(localStorage.getItem(PREFS_KEY) || '{}') };
    } catch {
        return { font: 'serif', size: 110 };
    }
}

function storePrefs(prefs) {
    try {
        localStorage.setItem(PREFS_KEY, JSON.stringify(prefs));
    } catch {
        // Preferences are a convenience only.
    }
}

/**
 * The page's @font-face rules, with absolute URLs, so the book's iframe can use the same fonts.
 */
function fontFaceCss() {
    let css = '';

    for (const sheet of document.styleSheets) {
        let rules;

        try {
            rules = sheet.cssRules;
        } catch {
            continue;
        }

        for (const rule of rules) {
            if (rule instanceof CSSFontFaceRule && /Literata|Plus Jakarta/i.test(rule.cssText)) {
                const base = sheet.href || window.location.href;
                css += rule.cssText.replace(/url\((['"]?)([^'")]+)\1\)/g, (match, quote, src) => `url("${new URL(src, base).href}")`) + '\n';
            }
        }
    }

    return css;
}

export async function initEpubReader(element) {
    const $ = (selector) => element.querySelector(selector);
    const viewer = $('[data-epub-viewer]');
    const url = element.dataset.progressUrl;
    const prefs = loadPrefs();
    let lastLocation = null;

    let buffer;

    try {
        const response = await fetch(element.dataset.src, { credentials: 'same-origin' });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        buffer = await response.arrayBuffer();
    } catch {
        $('[data-epub-loading]').textContent = 'This book could not be loaded.';

        return;
    }

    const book = ePub(buffer);
    const rendition = book.renderTo(viewer, {
        width: '100%',
        height: '100%',
        spread: 'auto',
        allowScriptedContent: false,
    });

    const faces = fontFaceCss();

    rendition.hooks.content.register((contents) => {
        if (faces) {
            contents.addStylesheetCss(faces, 'rukre-fonts');
        }
    });

    rendition.themes.default({
        html: { 'color-scheme': 'dark', background: '#121214 !important' },
        body: { color: '#E5E1E4 !important', background: '#121214 !important', 'line-height': '1.75 !important' },
        'p, li, blockquote': { color: '#E5E1E4 !important' },
        'h1, h2, h3, h4': { color: '#FFFFFF !important' },
        a: { color: '#FFD300 !important' },
        '::selection': { background: 'rgba(255, 211, 0, 0.35)' },
    });

    const applyPrefs = () => {
        rendition.themes.font(FONTS[prefs.font] || FONTS.serif);
        rendition.themes.fontSize(`${prefs.size}%`);
        $('[data-epub-font-label]').textContent = prefs.font === 'serif' ? 'Serif' : 'Sans';
        storePrefs(prefs);
    };

    applyPrefs();

    const display = rendition.display(element.dataset.location || undefined).catch(() => rendition.display());

    display.then(() => $('[data-epub-loading]').remove());

    book.loaded.navigation.then(({ toc }) => {
        const select = $('[data-epub-toc]');
        const add = (items, depth = 0) => items.forEach((chapter) => {
            const option = document.createElement('option');
            option.value = chapter.href;
            option.textContent = `${'  '.repeat(depth)}${chapter.label.trim()}`;
            select.append(option);
            add(chapter.subitems || [], depth + 1);
        });
        add(toc);
        select.addEventListener('change', () => select.value && rendition.display(select.value));
    });

    const locations = display.then(() => book.locations.generate(1600)).catch(() => null);

    const save = (options = {}) => {
        if (!lastLocation) {
            return;
        }

        const cfi = lastLocation.start.cfi;
        const progress = book.locations.length() ? book.locations.percentageFromCfi(cfi) : 0;

        saveProgress(url, {
            position: lastLocation.start.displayed?.page ?? 0,
            progress,
            location: cfi,
            completed: Boolean(lastLocation.atEnd),
        }, options);
    };

    let saveTimer;

    rendition.on('relocated', (location) => {
        lastLocation = location;

        locations.then(() => {
            if (!book.locations.length()) {
                return;
            }

            const percent = Math.round(book.locations.percentageFromCfi(location.start.cfi) * 100);
            $('[data-epub-bar]').style.width = `${percent}%`;
            $('[data-epub-percent]').textContent = `${percent}%`;
        });

        clearTimeout(saveTimer);
        saveTimer = setTimeout(() => locations.then(() => save()), 1200);
    });

    const onKey = (event) => {
        if (!element.isConnected) {
            document.removeEventListener('keyup', onKey);

            return;
        }

        if (event.key === 'ArrowLeft') {
            rendition.prev();
        }

        if (event.key === 'ArrowRight') {
            rendition.next();
        }
    };

    document.addEventListener('keyup', onKey);
    rendition.on('keyup', onKey);

    $('[data-epub-prev]').addEventListener('click', () => rendition.prev());
    $('[data-epub-next]').addEventListener('click', () => rendition.next());
    $('[data-epub-font]').addEventListener('click', () => {
        prefs.font = prefs.font === 'serif' ? 'sans' : 'serif';
        applyPrefs();
    });
    $('[data-epub-smaller]').addEventListener('click', () => {
        prefs.size = Math.max(70, prefs.size - 10);
        applyPrefs();
    });
    $('[data-epub-larger]').addEventListener('click', () => {
        prefs.size = Math.min(200, prefs.size + 10);
        applyPrefs();
    });

    // Swipe between pages on touch screens.
    let touchX = null;
    rendition.on('touchstart', (event) => { touchX = event.changedTouches[0].screenX; });
    rendition.on('touchend', (event) => {
        if (touchX === null) {
            return;
        }

        const delta = event.changedTouches[0].screenX - touchX;
        touchX = null;

        if (Math.abs(delta) > 50) {
            delta < 0 ? rendition.next() : rendition.prev();
        }
    });

    onLeave(element, () => {
        clearTimeout(saveTimer);
        save({ beacon: true });
        rendition.destroy();
    });
}
