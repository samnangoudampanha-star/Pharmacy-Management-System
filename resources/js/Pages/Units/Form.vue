<template>
    <div>
        <Head :title="form.id ? $t('common.edit') : $t('common.create')" />
        <div class="card">
            <div class="card-header bg-white d-flex">
                <h5 class="mb-0">{{ $t('menu.units') }}</h5>
                <Link :href="route('admin.units.index')" class="btn btn-outline-secondary btn-sm ms-auto">
                    <i class="bi bi-arrow-left me-1"></i>{{ $t('common.back') }}
                </Link>
            </div>
            <form class="card-body" @submit.prevent="submit">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ $t('fields.name') }} <span class="text-danger">*</span></label>
                        <input v-model="form.name" class="form-control" :class="{ 'is-invalid': form.errors.name }" required />
                        <div class="invalid-feedback">{{ form.errors.name }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ $t('fields.symbol') }} <span class="text-danger">*</span></label>
                        <input v-model="form.symbol" class="form-control" :class="{ 'is-invalid': form.errors.symbol }" required />
                        <div class="invalid-feedback">{{ form.errors.symbol }}</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ $t('fields.description') }}</label>
                        <textarea v-model="form.description" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-primary" :disabled="form.processing">{{ $t('common.save') }}</button>
                    <Link :href="route('admin.units.index')" class="btn btn-outline-secondary">{{ $t('common.cancel') }}</Link>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({ unit: { type: Object, default: null } });

const form = useForm({
    id: props.unit?.id || null,
    name: props.unit?.name || '',
    symbol: props.unit?.symbol || '',
    description: props.unit?.description || '',
});

function submit() {
    if (form.id) form.put(route('admin.units.update', form.id));
    else form.post(route('admin.units.store'));
}
</script>
