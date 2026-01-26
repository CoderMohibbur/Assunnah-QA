import './bootstrap';
import Alpine from 'alpinejs';

// ✅ Swiper imports (আপনারটা miss ছিল)
import Swiper from 'swiper';
import { Pagination, Autoplay } from 'swiper/modules';

window.Alpine = Alpine;

/** Toast Store */
Alpine.store('toast', {
    open: false,
    title: '',
    message: '',
    link: '',
    timer: null,

    show({ title = 'নোটিফিকেশন', message = '', link = '', duration = 6500 } = {}) {
        this.title = title;
        this.message = message;
        this.link = link;
        this.open = true;

        clearTimeout(this.timer);
        this.timer = setTimeout(() => this.hide(), duration);
    },

    hide() {
        this.open = false;
        this.title = '';
        this.message = '';
        this.link = '';
    },
});

// helper
window.toast = (payload) => Alpine.store('toast').show(payload);

//** Drawer Store */
Alpine.store('drawer', {
    sidebar: false,
    openSidebar() { this.sidebar = true; },
    closeSidebar() { this.sidebar = false; },
    toggleSidebar() { this.sidebar = !this.sidebar; },
});


Alpine.start();

/** ✅ Home page Swiper init */
let qaSwiperInstance = null;

function initHomeSwiper() {
    const el = document.querySelector('.qa-swiper');
    if (!el) return;

    // HMR / re-init safe
    if (qaSwiperInstance) {
        qaSwiperInstance.destroy(true, true);
        qaSwiperInstance = null;
    }

    const paginationEl = el.querySelector('.swiper-pagination');

    qaSwiperInstance = new Swiper(el, {
        modules: [Pagination, Autoplay],
        loop: true,
        slidesPerView: 1,
        spaceBetween: 0,
        autoplay: { delay: 2500, disableOnInteraction: false },
        pagination: { el: paginationEl, clickable: true },
    });
}

document.addEventListener('DOMContentLoaded', initHomeSwiper);
