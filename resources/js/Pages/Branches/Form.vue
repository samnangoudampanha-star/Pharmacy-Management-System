<template>
    <div>
        <Head :title="form.id ? $t('branches.edit') : $t('branches.create')" />

        <div class="card">
            <div class="card-header bg-white d-flex align-items-center">
                <h5 class="mb-0">{{ form.id ? $t('branches.edit') : $t('branches.create') }}</h5>
                <Link :href="route('admin.branches.index')" class="btn btn-outline-secondary btn-sm ms-auto">
                    <i class="bi bi-arrow-left me-1"></i>{{ $t('common.back') }}
                </Link>
            </div>
            <form class="card-body" @submit.prevent="submit">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">{{ $t('fields.code') }} <span class="text-danger">*</span></label>
                        <input v-model="form.code" class="form-control" :class="{ 'is-invalid': form.errors.code }" required />
                        <div class="invalid-feedback">{{ form.errors.code }}</div>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">{{ $t('fields.name') }} <span class="text-danger">*</span></label>
                        <input v-model="form.name" class="form-control" :class="{ 'is-invalid': form.errors.name }" required />
                        <div class="invalid-feedback">{{ form.errors.name }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ $t('fields.phone') }}</label>
                        <input v-model="form.phone" class="form-control" :class="{ 'is-invalid': form.errors.phone }" />
                        <div class="invalid-feedback">{{ form.errors.phone }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ $t('fields.email') }}</label>
                        <input v-model="form.email" type="email" class="form-control" :class="{ 'is-invalid': form.errors.email }" />
                        <div class="invalid-feedback">{{ form.errors.email }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ $t('fields.city') }}</label>
                        <input v-model="form.city" class="form-control" />
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">{{ $t('fields.address') }}</label>
                        <input v-model="form.address" class="form-control" />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ $t('fields.country') }}</label>
                        <input v-model="form.country" class="form-control" />
                    </div>

                    <div class="col-md-3">
                        <div class="form-check form-switch mt-4">
                            <input id="is_main" v-model="form.is_main" type="checkbox" class="form-check-input" />
                            <label for="is_main" class="form-check-label">{{ $t('branches.is_main') }}</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check form-switch mt-4">
                            <input id="is_active" v-model="form.is_active" type="checkbox" class="form-check-input" />
                            <label for="is_active" class="form-check-label">{{ $t('common.active') }}</label>
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-primary" :disabled="form.processing">
                        <i class="bi bi-check2 me-1"></i>{{ $t('common.save') }}
                    </button>
                    <Link :href="route('admin.branches.index')" class="btn btn-outline-secondary">
                        {{ $t('common.cancel') }}
                    </Link>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    branch: { type: Object, default: null },
});

const form = useForm({
    id: props.branch?.id || null,
    code: props.branch?.code || '',
    name: props.branch?.name || '',
    phone: props.branch?.phone || '',
    email: props.branch?.email || '',
    address: props.branch?.address || '',
    city: props.branch?.city || '',
    country: props.branch?.country || '',
    is_main: props.branch?.is_main || false,
    is_active: props.branch?.is_active ?? true,
});

function submit() {
    if (form.id) {
        form.put(route('admin.branches.update', form.id));
    } else {
        form.post(route('admin.branches.store'));
    }
}
</script>
