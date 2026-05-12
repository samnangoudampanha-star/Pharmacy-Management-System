<template>
    <header class="top-header">
        <nav class="navbar navbar-expand p-0">
            <button
                type="button"
                class="btn btn-link text-dark d-lg-none me-2"
                @click="$emit('toggle-sidebar')"
                aria-label="Toggle sidebar"
            >
                <i class="bi bi-list fs-4"></i>
            </button>
            <div class="top-navbar d-none d-xl-block">
                <span class="fw-semibold text-secondary">{{ $t('app.name') }}</span>
            </div>

            <div class="top-navbar-right ms-auto d-flex align-items-center gap-2">
                <LanguageSwitcher />

                <div class="nav-item dropdown lang-switcher">
                    <button
                        class="btn btn-light dropdown-toggle d-flex align-items-center gap-2"
                        type="button"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                    >
                        <i class="bi bi-person-circle"></i>
                        <span class="d-none d-sm-inline">{{ user?.name || 'Admin' }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <Link class="dropdown-item" :href="route('admin.dashboard')">
                                <i class="bi bi-speedometer2 me-2"></i>{{ $t('menu.dashboard') }}
                            </Link>
                        </li>
                        <li><hr class="dropdown-divider" /></li>
                        <li>
                            <a class="dropdown-item" href="#" @click.prevent="logout">
                                <i class="bi bi-box-arrow-right me-2"></i>{{ $t('menu.logout') }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
</template>

<script setup>
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';

defineEmits(['toggle-sidebar']);

const page = usePage();
const user = computed(() => page.props.auth?.user);

function logout() {
    router.post(route('logout'));
}
</script>
