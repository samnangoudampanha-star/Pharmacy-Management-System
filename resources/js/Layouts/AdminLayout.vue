<template>
    <div class="wrapper" :class="{ 'sidebar-open': sidebarOpen }">
        <SidebarNav :open="sidebarOpen" @close="sidebarOpen = false" />
        <TopHeader @toggle-sidebar="sidebarOpen = !sidebarOpen" />

        <main class="page-content">
            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="breadcrumb-title pe-3">{{ $t('common.dashboard') }}</div>
                <div class="ps-3" v-if="breadcrumbs.length">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li
                                v-for="(item, i) in breadcrumbs"
                                :key="i"
                                class="breadcrumb-item"
                                :class="{ active: i === breadcrumbs.length - 1 }"
                                :aria-current="i === breadcrumbs.length - 1 ? 'page' : undefined"
                            >
                                <Link v-if="item.href && i !== breadcrumbs.length - 1" :href="item.href">
                                    {{ item.label }}
                                </Link>
                                <span v-else>{{ item.label }}</span>
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>

            <FlashMessages />

            <slot />
        </main>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import SidebarNav from '@/Components/SidebarNav.vue';
import TopHeader from '@/Components/TopHeader.vue';
import FlashMessages from '@/Components/FlashMessages.vue';
import { setLocale, state } from '@/i18n';

const sidebarOpen = ref(false);
const page = usePage();

const breadcrumbs = computed(() => page.props.breadcrumbs || []);

watch(
    () => page.props.locale,
    (locale) => {
        if (locale) setLocale(locale);
    },
    { immediate: true }
);

onMounted(() => {
    setLocale(state.locale);
});
</script>
