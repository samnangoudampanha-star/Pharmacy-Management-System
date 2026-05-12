<template>
    <aside class="sidebar-wrapper" :class="{ 'is-open': open }">
        <div class="sidebar-header">
            <i class="bi bi-capsule fs-3 text-warning"></i>
            <h4 class="logo-text">{{ $t("app.short_name") }}</h4>
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
                    <span class="parent-icon"
                        ><i :class="['bi', item.icon]"></i
                    ></span>
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
                        <span class="parent-icon"
                            ><i :class="['bi', item.icon]"></i
                        ></span>
                        <span class="menu-title grow text-start">{{
                            $t(item.label)
                        }}</span>
                        <i
                            class="bi ms-auto"
                            :class="
                                openIndex === idx
                                    ? 'bi-chevron-up'
                                    : 'bi-chevron-down'
                            "
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
import { ref, computed } from "vue";
import { usePage } from "@inertiajs/vue3";

defineProps({ open: { type: Boolean, default: false } });
defineEmits(["close"]);

const page = usePage();
const currentUrl = computed(() => page.url);
const authUser = computed(() => page.props.auth?.user || null);

function can(permission) {
    if (!authUser.value) return false;
    if (authUser.value.is_admin) return true;
    return (authUser.value.permissions || []).includes(permission);
}

const menu = computed(() =>
    [
        can("dashboard.view")
            ? {
                  label: "menu.dashboard",
                  icon: "bi-house-door",
                  href: route("admin.dashboard"),
              }
            : null,
        {
            label: "menu.masters",
            icon: "bi-collection",
            children: [
                can("branches.manage")
                    ? {
                          label: "menu.branches",
                          href: route("admin.branches.index"),
                      }
                    : null,
                can("categories.manage")
                    ? {
                          label: "menu.categories",
                          href: route("admin.categories.index"),
                      }
                    : null,
                can("units.manage")
                    ? { label: "menu.units", href: route("admin.units.index") }
                    : null,
                can("manufacturers.manage")
                    ? {
                          label: "menu.manufacturers",
                          href: route("admin.manufacturers.index"),
                      }
                    : null,
            ].filter(Boolean),
        },
        {
            label: "menu.people",
            icon: "bi-people",
            children: [
                can("users.manage")
                    ? { label: "menu.users", href: route("admin.users.index") }
                    : null,
                can("roles.manage")
                    ? { label: "menu.roles", href: route("admin.roles.index") }
                    : null,
                can("permissions.manage")
                    ? {
                          label: "menu.permissions",
                          href: route("admin.permissions.index"),
                      }
                    : null,
                can("suppliers.manage")
                    ? {
                          label: "menu.suppliers",
                          href: route("admin.suppliers.index"),
                      }
                    : null,
                can("customers.manage")
                    ? {
                          label: "menu.customers",
                          href: route("admin.customers.index"),
                      }
                    : null,
            ].filter(Boolean),
        },
        {
            label: "menu.inventory",
            icon: "bi-box-seam",
            children: [
                can("products.manage")
                    ? {
                          label: "menu.products",
                          href: route("admin.products.index"),
                      }
                    : null,
                can("stocks.view")
                    ? {
                          label: "menu.stocks",
                          href: route("admin.stocks.index"),
                      }
                    : null,
                can("stock_transfers.manage")
                    ? {
                          label: "menu.stock_transfers",
                          href: route("admin.stock-transfers.index"),
                      }
                    : null,
                can("stock_adjustments.manage")
                    ? {
                          label: "menu.stock_adjustments",
                          href: route("admin.stock-adjustments.index"),
                      }
                    : null,
            ].filter(Boolean),
        },
        {
            label: "menu.operations",
            icon: "bi-cart-check",
            children: [
                can("purchases.manage")
                    ? {
                          label: "menu.purchases",
                          href: route("admin.purchases.index"),
                      }
                    : null,
                can("sales.manage")
                    ? { label: "menu.sales", href: route("admin.sales.index") }
                    : null,
                can("prescriptions.manage")
                    ? {
                          label: "menu.prescriptions",
                          href: route("admin.prescriptions.index"),
                      }
                    : null,
            ].filter(Boolean),
        },
        {
            label: "menu.expenses",
            icon: "bi-cash-coin",
            children: [
                can("expense_categories.manage")
                    ? {
                          label: "menu.expense_categories",
                          href: route("admin.expense-categories.index"),
                      }
                    : null,
                can("expenses.manage")
                    ? {
                          label: "menu.expenses",
                          href: route("admin.expenses.index"),
                      }
                    : null,
                can("payments.manage")
                    ? {
                          label: "menu.payments",
                          href: route("admin.payments.index"),
                      }
                    : null,
            ].filter(Boolean),
        },
    ].filter(
        (item) =>
            item && (item.href || (item.children && item.children.length)),
    ),
);

const openIndex = ref(null);
function toggle(idx) {
    openIndex.value = openIndex.value === idx ? null : idx;
}
function isActive(item) {
    if (!item.href) return false;
    const url = new URL(item.href, window.location.origin);
    return (
        currentUrl.value === url.pathname ||
        currentUrl.value.startsWith(url.pathname + "/")
    );
}
function hasActiveChild(item) {
    return (item.children || []).some(isActive);
}
</script>
