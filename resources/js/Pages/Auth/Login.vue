<template>
    <div class="d-flex align-items-center justify-content-center min-vh-100 bg-light">
        <Head :title="$t('menu.logout') + ' / Login'" />
        <div class="card shadow-sm" style="min-width: 360px; max-width: 420px;">
            <div class="card-body p-4">
                <div class="text-center mb-3">
                    <i class="bi bi-capsule fs-1 text-warning"></i>
                    <h4 class="mt-2">{{ $t('app.name') }}</h4>
                </div>
                <form @submit.prevent="submit">
                    <div class="mb-3">
                        <label class="form-label">{{ $t('fields.email') }}</label>
                        <input
                            v-model="form.email"
                            type="email"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.email }"
                            required
                            autofocus
                        />
                        <div class="invalid-feedback">{{ form.errors.email }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ $t('fields.password') }}</label>
                        <input
                            v-model="form.password"
                            type="password"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.password }"
                            required
                        />
                        <div class="invalid-feedback">{{ form.errors.password }}</div>
                    </div>
                    <div class="form-check mb-3">
                        <input v-model="form.remember" class="form-check-input" type="checkbox" id="remember" />
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    <button class="btn btn-primary w-100" :disabled="form.processing">
                        {{ form.processing ? $t('common.loading') : 'Sign in' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

defineOptions({ layout: null });

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

function submit() {
    form.post(route('login'));
}
</script>
