<template>
    <aside class="sidebar-wrapper" :class="{ 'is-open': open }">
        <div class="sidebar-header">
            <i class="bi bi-capsule fs-3 text-warning"></i>
            <h4 class="logo-text">{{ $t('app.short_name') }}</h4>
            <button
                type="button"
                class="btn btn-sm btn-link text-white ms-auto d-lg-none"
                @click="$emit('close')"
                aria-label="Close sidebar"
            >
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <ul class="metismenu" id="menu">
            <li v-for="(item, idx) in menu" :key="idx">
                <Link
                    v-if="item.href"
                    :href="item.href"
                    :class="{ active: isActive(item) }"
                    @click="$emit('close')"
                >
                    <span class="parent-icon"><i :class="['bi', item.icon]"></i></span>
                    <span class="menu-title">{{ $t(item.label) }}</span>
                </Link>

                <template v-else>
                    <button
                        type="button"
                        class="has-arrow"
                        :class="{ active: hasActiveChild(item) }"
                        :aria-expanded="openIndex === idx"
                        @click="toggle(idx)"
                    >
                        <span class="parent-icon"><i :class="['bi', item.icon]"></i></span>
                        <span class="menu-title flex-grow-1 text-start">{{ $t(item.label) }}</span>
                        <i
                            class="bi ms-auto"
                            :class="openIndex === idx ? 'bi-chevron-up' : 'bi-chevron-down'"
                        ></i>
                    </button>
                    <ul v-show="openIndex === idx || hasActiveChild(item)">
                        <li v-for="child in item.children" :key="child.href">
                            <Link
                                :href="child.href"
                                :class="{ active: isActive(child) }"
                                @click="$emit('close')"
                            >
                                <i class="bi bi-arrow-right-short"></i>
                                {{ $t(child.label) }}
                            </Link>
                        </li>
                    </ul>
                </template>
            </li>
        </ul>
    </aside>
</template>

<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

defineProps({ open: { type: Boolean, default: false } });
defineEmits(['close']);

const page = usePage();
const currentUrl = computed(() => page.url);

const menu = computed(() => [
    { label: 'menu.dashboard', icon: 'bi-house-door', href: route('admin.dashboard') },
    {
        label: 'menu.masters',
        icon: 'bi-collection',
        children: [
            { label: 'menu.branches', href: route('admin.branches.index') },
            { label: 'menu.categories', href: route('admin.categories.index') },
            { label: 'menu.units', href: route('admin.units.index') },
            { label: 'menu.manufacturers', href: route('admin.manufacturers.index') },
        ],
    },
    {
        label: 'menu.people',
        icon: 'bi-people',
        children: [
            { label: 'menu.users', href: route('admin.users.index') },
            { label: 'menu.suppliers', href: route('admin.suppliers.index') },
            { label: 'menu.customers', href: route('admin.customers.index') },
        ],
    },
    {
        label: 'menu.inventory',
        icon: 'bi-box-seam',
        children: [
            { label: 'menu.products', href: route('admin.products.index') },
            { label: 'menu.stocks', href: route('admin.stocks.index') },
            { label: 'menu.stock_transfers', href: route('admin.stock-transfers.index') },
            { label: 'menu.stock_adjustments', href: route('admin.stock-adjustments.index') },
        ],
    },
    {
        label: 'menu.operations',
        icon: 'bi-cart-check',
        children: [
            { label: 'menu.purchases', href: route('admin.purchases.index') },
            { label: 'menu.sales', href: route('admin.sales.index') },
            { label: 'menu.prescriptions', href: route('admin.prescriptions.index') },
        ],
    },
    {
        label: 'menu.expenses',
        icon: 'bi-cash-coin',
        children: [
            { label: 'menu.expense_categories', href: route('admin.expense-categories.index') },
            { label: 'menu.expenses', href: route('admin.expenses.index') },
        ],
    },
]);

const openIndex = ref(null);
function toggle(idx) {
    openIndex.value = openIndex.value === idx ? null : idx;
}
function isActive(item) {
    if (!item.href) return false;
    const url = new URL(item.href, window.location.origin);
    return currentUrl.value === url.pathname || currentUrl.value.startsWith(url.pathname + '/');
}
function hasActiveChild(item) {
    return (item.children || []).some(isActive);
}
</script>
