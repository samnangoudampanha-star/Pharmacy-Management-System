<template>
    <div>
        <Head :title="form.id ? $t('products.edit') : $t('products.create')" />
        <div class="card">
            <div class="card-header bg-white d-flex">
                <h5 class="mb-0">{{ form.id ? $t('products.edit') : $t('products.create') }}</h5>
                <Link :href="route('admin.products.index')" class="btn btn-outline-secondary btn-sm ms-auto">
                    <i class="bi bi-arrow-left me-1"></i>{{ $t('common.back') }}
                </Link>
            </div>
            <form class="card-body" @submit.prevent="submit">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">{{ $t('fields.sku') }} <span class="text-danger">*</span></label>
                        <input v-model="form.sku" class="form-control" :class="{ 'is-invalid': form.errors.sku }" required />
                        <div class="invalid-feedback">{{ form.errors.sku }}</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ $t('fields.barcode') }}</label>
                        <input v-model="form.barcode" class="form-control" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ $t('fields.name') }} <span class="text-danger">*</span></label>
                        <input v-model="form.name" class="form-control" :class="{ 'is-invalid': form.errors.name }" required />
                        <div class="invalid-feedback">{{ form.errors.name }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ $t('fields.generic_name') }}</label>
                        <input v-model="form.generic_name" class="form-control" />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ $t('fields.category') }}</label>
                        <TomSelectInput v-model="form.category_id" :options="catOpts" :placeholder="$t('common.select')" />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ $t('fields.unit') }}</label>
                        <TomSelectInput v-model="form.unit_id" :options="unitOpts" :placeholder="$t('common.select')" />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ $t('fields.manufacturer') }}</label>
                        <TomSelectInput v-model="form.manufacturer_id" :options="manOpts" :placeholder="$t('common.select')" />
                    </div>
                    <div class="col-md-2"><label class="form-label">{{ $t('fields.cost_price') }}</label><input v-model.number="form.cost_price" type="number" step="0.0001" class="form-control" /></div>
                    <div class="col-md-2"><label class="form-label">{{ $t('fields.sale_price') }}</label><input v-model.number="form.sale_price" type="number" step="0.0001" class="form-control" /></div>
                    <div class="col-md-2"><label class="form-label">{{ $t('fields.tax_rate') }} (%)</label><input v-model.number="form.tax_rate" type="number" step="0.01" class="form-control" /></div>
                    <div class="col-md-2"><label class="form-label">{{ $t('fields.reorder_level') }}</label><input v-model.number="form.reorder_level" type="number" step="1" class="form-control" /></div>
                    <div class="col-md-2"><label class="form-label">{{ $t('fields.batch_number') }}</label><input v-model="form.batch_number" class="form-control" /></div>
                    <div class="col-md-3">
                        <label class="form-label">{{ $t('fields.manufacture_date') }}</label>
                        <FlatpickrInput v-model="form.manufacture_date" date-format="Y-m-d" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ $t('fields.expiry_date') }}</label>
                        <FlatpickrInput v-model="form.expiry_date" date-format="Y-m-d" />
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
                    <Link :href="route('admin.products.index')" class="btn btn-outline-secondary">{{ $t('common.cancel') }}</Link>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import FlatpickrInput from '@/Components/FlatpickrInput.vue';
import TomSelectInput from '@/Components/TomSelectInput.vue';

const props = defineProps({
    product: { type: Object, default: null },
    options: { type: Object, required: true },
});

const catOpts = computed(() => props.options.categories.map((c) => ({ value: c.id, label: c.name })));
const unitOpts = computed(() => props.options.units.map((u) => ({ value: u.id, label: u.symbol ? `${u.name} (${u.symbol})` : u.name })));
const manOpts = computed(() => props.options.manufacturers.map((m) => ({ value: m.id, label: m.name })));

const form = useForm({
    id: props.product?.id || null,
    sku: props.product?.sku || '',
    barcode: props.product?.barcode || '',
    name: props.product?.name || '',
    generic_name: props.product?.generic_name || '',
    category_id: props.product?.category_id || null,
    unit_id: props.product?.unit_id || null,
    manufacturer_id: props.product?.manufacturer_id || null,
    cost_price: Number(props.product?.cost_price || 0),
    sale_price: Number(props.product?.sale_price || 0),
    tax_rate: Number(props.product?.tax_rate || 0),
    reorder_level: Number(props.product?.reorder_level || 0),
    manufacture_date: props.product?.manufacture_date || '',
    expiry_date: props.product?.expiry_date || '',
    batch_number: props.product?.batch_number || '',
    description: props.product?.description || '',
    is_active: props.product?.is_active ?? true,
});

function submit() {
    if (form.id) form.put(route('admin.products.update', form.id));
    else form.post(route('admin.products.store'));
}
</script>
