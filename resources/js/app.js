import './bootstrap';

import { createApp, h } from 'vue';
import { createInertiaApp, Link, Head } from '@inertiajs/vue3';
import { ZiggyVue } from 'ziggy-js';
import * as bootstrap from 'bootstrap';

import { i18n, setLocale } from './i18n';
import AdminLayout from './Layouts/AdminLayout.vue';

window.bootstrap = bootstrap;

createInertiaApp({
    title: (title) => (title ? `${title} | Pharmacy MS` : 'Pharmacy Management System'),
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        const page = pages[`./Pages/${name}.vue`];
        if (!page) {
            throw new Error(`Inertia page not found: ./Pages/${name}.vue`);
        }
        page.default.layout = page.default.layout || AdminLayout;
        return page;
    },
    setup({ el, App, props, plugin }) {
        const initialLocale =
            props?.initialPage?.props?.locale || document.documentElement.lang || 'en';
        setLocale(initialLocale);

        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(i18n);

        app.component('Link', Link);
        app.component('Head', Head);

        app.mount(el);
    },
    progress: { color: '#0d6efd' },
});
