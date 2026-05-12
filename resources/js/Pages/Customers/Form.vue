<template>
    <div>
        <Head :title="form.id ? $t('common.edit') : $t('common.create')" />
        <div class="card">
            <div class="card-header bg-white d-flex">
                <h5 class="mb-0">{{ $t('menu.customers') }}</h5>
                <Link :href="route('admin.customers.index')" class="btn btn-outline-secondary btn-sm ms-auto">
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
                    <div class="col-md-4"><label class="form-label">{{ $t('fields.phone') }}</label><input v-model="form.phone" class="form-control" /></div>
                    <div class="col-md-4"><label class="form-label">{{ $t('fields.email') }}</label><input v-model="form.email" type="email" class="form-control" /></div>
                    <div class="col-md-4">
                        <label class="form-label">DOB</label>
                        <FlatpickrInput v-model="form.date_of_birth" date-format="Y-m-d" />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Gender</label>
                        <TomSelectInput v-model="form.gender" :options="genderOptions" :placeholder="$t('common.select')" />
                    </div>
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
                    <Link :href="route('admin.customers.index')" class="btn btn-outline-secondary">{{ $t('common.cancel') }}</Link>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import FlatpickrInput from '@/Components/FlatpickrInput.vue';
import TomSelectInput from '@/Components/TomSelectInput.vue';

const props = defineProps({ customer: { type: Object, default: null } });

const genderOptions = [
    { value: 'male', label: 'Male' },
    { value: 'female', label: 'Female' },
    { value: 'other', label: 'Other' },
];

const form = useForm({
    id: props.customer?.id || null,
    code: props.customer?.code || '',
    name: props.customer?.name || '',
    phone: props.customer?.phone || '',
    email: props.customer?.email || '',
    address: props.customer?.address || '',
    date_of_birth: props.customer?.date_of_birth || '',
    gender: props.customer?.gender || '',
    opening_balance: Number(props.customer?.opening_balance || 0),
    is_active: props.customer?.is_active ?? true,
});

function submit() {
    if (form.id) form.put(route('admin.customers.update', form.id));
    else form.post(route('admin.customers.store'));
}
</script>
