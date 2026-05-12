import { reactive, computed } from 'vue';
import en from './translations/en';
import km from './translations/km';

const messages = { en, km };

export const state = reactive({
    locale: 'en',
});

export function setLocale(locale) {
    if (!messages[locale]) return;
    state.locale = locale;
    if (typeof document !== 'undefined') {
        document.documentElement.lang = locale;
        document.documentElement.setAttribute('data-locale', locale);
        document.body?.setAttribute('data-locale', locale);
    }
}

function resolve(obj, path) {
    return path.split('.').reduce((acc, part) => (acc && acc[part] !== undefined ? acc[part] : null), obj);
}

export function trans(key, replacements = {}) {
    const dict = messages[state.locale] || messages.en;
    let value = resolve(dict, key);
    if (value == null) value = resolve(messages.en, key);
    if (value == null) return key;
    Object.keys(replacements).forEach((r) => {
        value = value.replace(new RegExp(`:${r}`, 'g'), replacements[r]);
    });
    return value;
}

export const i18n = {
    install(app) {
        app.config.globalProperties.$t = trans;
        app.config.globalProperties.$locale = computed(() => state.locale);
        app.provide('i18n', { trans, state, setLocale });
    },
};

export default i18n;
