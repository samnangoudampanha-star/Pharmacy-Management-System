<template>
    <div>
        <Head :title="form.id ? $t('common.edit') : $t('common.create')" />
        <div class="card">
            <div class="card-header bg-white d-flex">
                <h5 class="mb-0">{{ $t('menu.expense_categories') }}</h5>
                <Link :href="route('admin.expense-categories.index')" class="btn btn-outline-secondary btn-sm ms-auto">
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
                    <div class="col-12">
                        <label class="form-label">{{ $t('fields.description') }}</label>
                        <textarea v-model="form.description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input id="active" v-model="form.is_active" type="checkbox" class="form-check-input" />
                            <label for="active" class="form-check-label">{{ $t('common.active') }}</label>
                        </div>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-primary" :disabled="form.processing">{{ $t('common.save') }}</button>
                    <Link :href="route('admin.expense-categories.index')" class="btn btn-outline-secondary">{{ $t('common.cancel') }}</Link>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({ category: { type: Object, default: null } });

const form = useForm({
    id: props.category?.id || null,
    name: props.category?.name || '',
    description: props.category?.description || '',
    is_active: props.category?.is_active ?? true,
});

function submit() {
    if (form.id) form.put(route('admin.expense-categories.update', form.id));
    else form.post(route('admin.expense-categories.store'));
}
</script>
