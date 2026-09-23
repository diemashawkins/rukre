import { onLeave, saveProgress } from './progress';

/**
 * PDFs open in the browser's own viewer; we only record that the book was opened
 * so it shows up under "Continue" on the home page.
 */
export function initPdfReader(element) {
    saveProgress(element.dataset.progressUrl, { position: 1, progress: 0.02, location: 'opened' });
}

/**
 * CBZ comics and webtoons: one continuous vertical scroll of pages.
 */
export function initComicReader(element) {
    const url = element.dataset.progressUrl;
    const total = Number(element.dataset.pageCount || 0);
    const resume = Number(element.dataset.resume || 0);
    const pages = [...element.querySelectorAll('[data-page]')];
    let currentPage = 0;
    let saveTimer;

    const save = (options = {}) => {
        if (!total) {
            return;
        }

        saveProgress(url, {
            position: currentPage,
            progress: (currentPage + 1) / total,
            completed: currentPage >= total - 1,
        }, options);
    };

    if (resume > 0 && pages[resume]) {
        // Load everything up to the saved page so the scroll position is right.
        pages.slice(0, resume + 1).forEach((page) => { page.loading = 'eager'; });
        pages[resume].addEventListener('load', () => pages[resume].scrollIntoView({ block: 'start' }), { once: true });
    }

    const observer = new IntersectionObserver((entries) => {
        for (const entry of entries) {
            if (entry.isIntersecting) {
                currentPage = Number(entry.target.dataset.page);
                clearTimeout(saveTimer);
                saveTimer = setTimeout(save, 1500);
            }
        }
    }, { threshold: 0.5 });

    pages.forEach((page) => observer.observe(page));

    onLeave(element, () => {
        clearTimeout(saveTimer);
        save({ beacon: true });
        observer.disconnect();
    });
}

/**
 * Plain-text books, shown in the serif "editorial" reading style.
 */
export async function initTextReader(element) {
    const url = element.dataset.progressUrl;
    let saveTimer;

    try {
        const response = await fetch(element.dataset.src, { credentials: 'same-origin' });
        element.textContent = await response.text();
    } catch {
        element.textContent = 'This file could not be loaded.';

        return;
    }

    const fraction = () => {
        const rect = element.getBoundingClientRect();
        const scrollable = rect.height - window.innerHeight;

        return scrollable > 0 ? Math.min(1, Math.max(0, -rect.top / scrollable)) : 1;
    };

    const resume = Number(element.dataset.resume || 0);

    if (resume > 0) {
        const rect = element.getBoundingClientRect();
        window.scrollTo({ top: window.scrollY + rect.top + (rect.height - window.innerHeight) * resume });
    }

    const save = (options = {}) => {
        const progress = fraction();
        saveProgress(url, { position: Math.round(progress * 1000), progress, completed: progress > 0.99 }, options);
    };

    const onScroll = () => {
        if (!element.isConnected) {
            window.removeEventListener('scroll', onScroll);

            return;
        }

        clearTimeout(saveTimer);
        saveTimer = setTimeout(save, 1500);
    };

    window.addEventListener('scroll', onScroll, { passive: true });
    onLeave(element, () => save({ beacon: true }));
}
