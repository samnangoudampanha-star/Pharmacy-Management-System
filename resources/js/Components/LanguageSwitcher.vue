<template>
    <div class="dropdown lang-switcher">
        <button
            class="dropdown-toggle d-flex align-items-center gap-2"
            type="button"
            data-bs-toggle="dropdown"
            aria-expanded="false"
        >
            <i class="bi bi-globe2"></i>
            <span>{{ current.label }}</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li v-for="locale in locales" :key="locale.code">
                <a
                    href="#"
                    class="dropdown-item d-flex align-items-center gap-2"
                    :class="{ active: state.locale === locale.code }"
                    @click.prevent="switchTo(locale.code)"
                >
                    <span>{{ locale.flag }}</span>
                    <span>{{ locale.label }}</span>
                </a>
            </li>
        </ul>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';
import { state, setLocale } from '@/i18n';

const locales = [
    { code: 'en', label: 'English', flag: 'EN' },
    { code: 'km', label: 'ខ្មែរ', flag: 'KM' },
];

const current = computed(
    () => locales.find((l) => l.code === state.locale) || locales[0]
);

function switchTo(code) {
    setLocale(code);
    // Persist locale on server without a full page reload.
    axios
        .post(route('locale.switch'), { locale: code })
        .then(() => {
            // Reload only the `locale` prop so other tabs/components stay synced
            router.reload({ only: ['locale'], preserveScroll: true, preserveState: true });
        })
        .catch(() => {
            /* swallow – UI already reflects the change */
        });
}
</script>
