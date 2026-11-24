// axios (resources/js/bootstrap.js) is not needed by the public site, so it is not bundled.
// CSP-safe Alpine build: no eval, so it works under the strict Content-Security-Policy.
import Alpine from '@alpinejs/csp';
import Swiper from 'swiper';
import { A11y, Autoplay, EffectFade, Keyboard, Navigation, Pagination } from 'swiper/modules';

Alpine.data('mobileNav', () => ({
    open: false,
    toggle() {
        this.open = !this.open;
    },
    close() {
        this.open = false;
    },
}));

window.Alpine = Alpine;
Alpine.start();

// Click-to-load YouTube: nothing is requested from YouTube until the visitor presses play.
document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-video-id]');
    if (!button) return;
    const id = button.dataset.videoId;
    if (!/^[A-Za-z0-9_-]{11}$/.test(id)) return;
    const iframe = document.createElement('iframe');
    iframe.src = `https://www.youtube-nocookie.com/embed/${id}?autoplay=1`;
    iframe.title = button.dataset.videoTitle || 'Video';
    iframe.allow = 'autoplay; encrypted-media; picture-in-picture';
    iframe.allowFullscreen = true;
    button.replaceWith(iframe);
});

// Sliders (Swiper). Autoplay is switched off for visitors who prefer reduced motion.
const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

// Full-width home slider: gentle cross-fade, loops, pauses on hover and while the pointer or focus is inside.
document.querySelectorAll('[data-hero-slider]').forEach((el) => {
    const slides = el.querySelectorAll('.swiper-slide').length;
    const bar = el.querySelector('.hero-progress');
    new Swiper(el, {
        modules: [Autoplay, Pagination, Navigation, EffectFade, A11y, Keyboard],
        on: {
            // Gold progress bar: fills as the time before the next slide runs out.
            autoplayTimeLeft(swiper, timeLeft, percentage) {
                if (bar) bar.style.transform = `scaleX(${1 - percentage})`;
            },
        },
        effect: 'fade',
        fadeEffect: { crossFade: true },
        loop: slides > 1,
        speed: 1100,
        allowTouchMove: slides > 1,
        keyboard: { enabled: true },
        autoplay: slides > 1 && !reduceMotion ? { delay: 6000, disableOnInteraction: false, pauseOnMouseEnter: true } : false,
        pagination: { el: el.querySelector('.swiper-pagination'), clickable: true },
        navigation: { nextEl: el.querySelector('.swiper-button-next'), prevEl: el.querySelector('.swiper-button-prev') },
        a11y: { prevSlideMessage: 'Previous slide', nextSlideMessage: 'Next slide', paginationBulletMessage: 'Go to slide {{index}}' },
    });
});

// Moving card slider (houses, prefects): 1 / 2 / 3 / 4 cards by screen width, same pacing as the St Monique's site.
document.querySelectorAll('[data-card-slider]').forEach((el) => {
    const count = el.querySelectorAll('.swiper-slide').length;
    const perView = Number(el.dataset.perView || 3);
    new Swiper(el, {
        modules: [Autoplay, Pagination, Navigation, A11y, Keyboard],
        slidesPerView: 1,
        spaceBetween: 24,
        loop: count > perView,
        speed: 1000,
        keyboard: { enabled: true },
        autoplay: count > 1 && !reduceMotion ? { delay: 4000, disableOnInteraction: false, pauseOnMouseEnter: true } : false,
        pagination: { el: el.querySelector('.swiper-pagination'), clickable: true },
        navigation: { nextEl: el.querySelector('.swiper-button-next'), prevEl: el.querySelector('.swiper-button-prev') },
        breakpoints: {
            640: { slidesPerView: Math.min(2, perView), spaceBetween: 20 },
            1024: { slidesPerView: Math.min(3, perView), spaceBetween: 28 },
            1280: { slidesPerView: perView, spaceBetween: 28 },
        },
        a11y: { prevSlideMessage: 'Previous', nextSlideMessage: 'Next' },
    });
});

// Simple accessible lightbox for content galleries.
const dialog = document.getElementById('lightbox');
if (dialog) {
    const img = dialog.querySelector('img');
    document.addEventListener('click', (event) => {
        const target = event.target.closest('.pc-gallery img, .is-photo-page img, img[data-full], [data-lightbox]');
        if (!target) return;
        // Open the larger version when one exists (data-full), otherwise whatever is on screen.
        img.src = target.dataset.full || target.currentSrc || target.src;
        img.alt = target.alt;
        dialog.showModal();
    });
    dialog.addEventListener('click', () => dialog.close());
}
