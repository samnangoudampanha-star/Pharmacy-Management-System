<template>
    <div>
        <Head :title="form.id ? $t('common.edit') : $t('common.create')" />
        <div class="card">
            <div class="card-header bg-white d-flex">
                <h5 class="mb-0">{{ $t('menu.stock_adjustments') }}</h5>
                <Link :href="route('admin.stock-adjustments.index')" class="btn btn-outline-secondary btn-sm ms-auto">
                    <i class="bi bi-arrow-left me-1"></i>{{ $t('common.back') }}
                </Link>
            </div>
            <form class="card-body" @submit.prevent="submit">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">{{ $t('fields.reference_number') }}</label>
                        <input v-model="form.reference_number" class="form-control" />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ $t('fields.branch') }} <span class="text-danger">*</span></label>
                        <TomSelectInput v-model="form.branch_id" :options="branchOpts" :placeholder="$t('common.select')" />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ $t('fields.product') }} <span class="text-danger">*</span></label>
                        <TomSelectInput v-model="form.product_id" :options="productOpts" :placeholder="$t('common.select')" />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date <span class="text-danger">*</span></label>
                        <FlatpickrInput v-model="form.adjustment_date" date-format="Y-m-d" />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ $t('fields.quantity') }} <span class="text-danger">*</span></label>
                        <input v-model.number="form.quantity" type="number" step="0.0001" class="form-control" required />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Type <span class="text-danger">*</span></label>
                        <TomSelectInput v-model="form.type" :options="typeOpts" />
                    </div>
                    <div class="col-12">
                        <label class="form-label">Reason</label>
                        <input v-model="form.reason" class="form-control" />
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-primary" :disabled="form.processing">{{ $t('common.save') }}</button>
                    <Link :href="route('admin.stock-adjustments.index')" class="btn btn-outline-secondary">{{ $t('common.cancel') }}</Link>
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
    adjustment: { type: Object, default: null },
    options: { type: Object, required: true },
});

const branchOpts = computed(() => props.options.branches.map((b) => ({ value: b.id, label: b.name })));
const productOpts = computed(() => props.options.products.map((p) => ({ value: p.id, label: `${p.sku} — ${p.name}` })));
const typeOpts = [
    { value: 'increase', label: 'Increase' },
    { value: 'decrease', label: 'Decrease' },
];

const form = useForm({
    id: props.adjustment?.id || null,
    reference_number: props.adjustment?.reference_number || '',
    branch_id: props.adjustment?.branch_id || null,
    product_id: props.adjustment?.product_id || null,
    adjustment_date: props.adjustment?.adjustment_date || new Date().toISOString().substring(0, 10),
    quantity: Number(props.adjustment?.quantity || 0),
    type: props.adjustment?.type || 'increase',
    reason: props.adjustment?.reason || '',
});

function submit() {
    if (form.id) form.put(route('admin.stock-adjustments.update', form.id));
    else form.post(route('admin.stock-adjustments.store'));
}
</script>
