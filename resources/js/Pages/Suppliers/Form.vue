<template>
    <div>
        <Head :title="form.id ? $t('common.edit') : $t('common.create')" />
        <div class="card">
            <div class="card-header bg-white d-flex">
                <h5 class="mb-0">{{ $t('menu.suppliers') }}</h5>
                <Link :href="route('admin.suppliers.index')" class="btn btn-outline-secondary btn-sm ms-auto">
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
                    <div class="col-md-4"><label class="form-label">Contact</label><input v-model="form.contact_person" class="form-control" /></div>
                    <div class="col-md-4"><label class="form-label">{{ $t('fields.phone') }}</label><input v-model="form.phone" class="form-control" /></div>
                    <div class="col-md-4"><label class="form-label">{{ $t('fields.email') }}</label><input v-model="form.email" type="email" class="form-control" /></div>
                    <div class="col-md-4"><label class="form-label">{{ $t('fields.city') }}</label><input v-model="form.city" class="form-control" /></div>
                    <div class="col-md-4"><label class="form-label">{{ $t('fields.country') }}</label><input v-model="form.country" class="form-control" /></div>
                    <div class="col-md-4">
                        <label class="form-label">{{ $t('fields.amount') }}</label>
                        <input v-model.number="form.opening_balance" type="number" step="0.01" class="form-control" />
                    </div>
                    <div class="col-12"><label class="form-label">{{ $t('fields.address') }}</label><input v-model="form.address" class="form-control" /></div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input id="active" v-model="form.is_active" type="checkbox" class="form-check-input" />
                            <label for="active" class="form-check-label">{{ $t('common.active') }}</label>
                        </div>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-primary" :disabled="form.processing">{{ $t('common.save') }}</button>
                    <Link :href="route('admin.suppliers.index')" class="btn btn-outline-secondary">{{ $t('common.cancel') }}</Link>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({ supplier: { type: Object, default: null } });

const form = useForm({
    id: props.supplier?.id || null,
    code: props.supplier?.code || '',
    name: props.supplier?.name || '',
    contact_person: props.supplier?.contact_person || '',
    phone: props.supplier?.phone || '',
    email: props.supplier?.email || '',
    address: props.supplier?.address || '',
    city: props.supplier?.city || '',
    country: props.supplier?.country || '',
    opening_balance: Number(props.supplier?.opening_balance || 0),
    is_active: props.supplier?.is_active ?? true,
});

function submit() {
    if (form.id) form.put(route('admin.suppliers.update', form.id));
    else form.post(route('admin.suppliers.store'));
}
</script>
