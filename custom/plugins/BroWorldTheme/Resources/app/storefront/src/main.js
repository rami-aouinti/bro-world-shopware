const STORAGE_KEY = 'bro-world-theme-mode';

function applyThemeMode(mode) {
    const root = document.documentElement;
    root.setAttribute('data-theme-mode', mode);

    if (mode === 'dark') {
        root.classList.add('is-dark-mode');
    } else {
        root.classList.remove('is-dark-mode');
    }
}

function getInitialMode() {
    const stored = window.localStorage.getItem(STORAGE_KEY);
    if (stored === 'dark' || stored === 'light') {
        return stored;
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

function handleThemeToggle(event) {
    event.preventDefault();
    const root = document.documentElement;
    const current = root.getAttribute('data-theme-mode') || 'light';
    const next = current === 'dark' ? 'light' : 'dark';

    applyThemeMode(next);
    window.localStorage.setItem(STORAGE_KEY, next);
}

function initialiseThemeMode() {
    const initial = getInitialMode();
    applyThemeMode(initial);

    document.querySelectorAll('[data-theme-toggle]').forEach((toggle) => {
        toggle.addEventListener('click', handleThemeToggle);
    });
}

function initialiseCarousels() {
    const carousels = document.querySelectorAll('[data-carousel]');
    carousels.forEach((carousel) => {
        const track = carousel.querySelector('[data-carousel-track]');
        if (!track) {
            return;
        }

        const slides = Array.from(track.querySelectorAll('[data-carousel-slide]'));
        if (slides.length <= 1) {
            return;
        }

        let index = 0;

        const update = () => {
            const offset = index * 100;
            track.style.transform = `translateX(-${offset}%)`;
            slides.forEach((slide, position) => {
                slide.classList.toggle('is-active', position === index);
            });
        };

        const nextButton = carousel.querySelector('[data-carousel-next]');
        const prevButton = carousel.querySelector('[data-carousel-prev]');

        if (nextButton) {
            nextButton.addEventListener('click', () => {
                index = (index + 1) % slides.length;
                update();
            });
        }

        if (prevButton) {
            prevButton.addEventListener('click', () => {
                index = (index - 1 + slides.length) % slides.length;
                update();
            });
        }

        let autoplayId = null;
        const autoplayDelay = Number(carousel.getAttribute('data-carousel-interval')) || 7000;

        const startAutoplay = () => {
            if (autoplayId !== null) {
                return;
            }
            autoplayId = window.setInterval(() => {
                index = (index + 1) % slides.length;
                update();
            }, autoplayDelay);
        };

        const stopAutoplay = () => {
            if (autoplayId !== null) {
                window.clearInterval(autoplayId);
                autoplayId = null;
            }
        };

        if (carousel.hasAttribute('data-carousel-autoplay')) {
            startAutoplay();
            carousel.addEventListener('mouseenter', stopAutoplay);
            carousel.addEventListener('mouseleave', startAutoplay);
        }

        update();
    });
}

function initialiseAnimations() {
    const animatedElements = document.querySelectorAll('[data-animate]');
    if (!animatedElements.length) {
        return;
    }

    const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                obs.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.15,
    });

    animatedElements.forEach((element) => observer.observe(element));
}

document.addEventListener('DOMContentLoaded', () => {
    initialiseThemeMode();
    initialiseCarousels();
    initialiseAnimations();
});

export {
    initialiseThemeMode,
    initialiseCarousels,
    initialiseAnimations,
};
