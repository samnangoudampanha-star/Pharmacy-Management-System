<template>
    <div>
        <Head :title="form.id ? $t('common.edit') : $t('common.create')" />
        <div class="card">
            <div class="card-header bg-white d-flex">
                <h5 class="mb-0">{{ $t('menu.prescriptions') }}</h5>
                <Link :href="route('admin.prescriptions.index')" class="btn btn-outline-secondary btn-sm ms-auto">
                    <i class="bi bi-arrow-left me-1"></i>{{ $t('common.back') }}
                </Link>
            </div>
            <form class="card-body" @submit.prevent="submit">
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label">{{ $t('fields.reference_number') }}</label>
                        <input v-model="form.reference_number" class="form-control" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ $t('fields.branch') }} <span class="text-danger">*</span></label>
                        <TomSelectInput v-model="form.branch_id" :options="branchOpts" :placeholder="$t('common.select')" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ $t('fields.customer') }}</label>
                        <TomSelectInput v-model="form.customer_id" :options="customerOpts" :placeholder="$t('common.select')" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date <span class="text-danger">*</span></label>
                        <FlatpickrInput v-model="form.prescription_date" date-format="Y-m-d" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Doctor</label>
                        <input v-model="form.doctor_name" class="form-control" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Diagnosis</label>
                        <input v-model="form.diagnosis" class="form-control" />
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="min-width: 220px;">{{ $t('fields.product') }}</th>
                                <th class="text-end" style="width: 110px;">{{ $t('fields.quantity') }}</th>
                                <th>Dosage</th>
                                <th>Instructions</th>
                                <th style="width: 50px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, idx) in form.items" :key="idx">
                                <td><TomSelectInput v-model="item.product_id" :options="productOpts" :placeholder="$t('fields.product')" /></td>
                                <td><input v-model.number="item.quantity" type="number" step="0.0001" class="form-control text-end" /></td>
                                <td><input v-model="item.dosage" class="form-control" /></td>
                                <td><input v-model="item.instructions" class="form-control" /></td>
                                <td><button type="button" class="btn btn-sm btn-outline-danger" @click="form.items.splice(idx, 1)"><i class="bi bi-x"></i></button></td>
                            </tr>
                            <tr v-if="!form.items.length"><td colspan="5" class="text-center text-muted py-3">{{ $t('common.no_data') }}</td></tr>
                        </tbody>
                        <tfoot>
                            <tr><td colspan="5"><button type="button" class="btn btn-sm btn-outline-primary" @click="addLine"><i class="bi bi-plus me-1"></i>Add line</button></td></tr>
                        </tfoot>
                    </table>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-12">
                        <label class="form-label">{{ $t('fields.notes') }}</label>
                        <textarea v-model="form.notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-primary" :disabled="form.processing">{{ $t('common.save') }}</button>
                    <Link :href="route('admin.prescriptions.index')" class="btn btn-outline-secondary">{{ $t('common.cancel') }}</Link>
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
    prescription: { type: Object, default: null },
    options: { type: Object, required: true },
});

const branchOpts = computed(() => props.options.branches.map((b) => ({ value: b.id, label: b.name })));
const customerOpts = computed(() => props.options.customers.map((c) => ({ value: c.id, label: c.name })));
const productOpts = computed(() => props.options.products.map((p) => ({ value: p.id, label: `${p.sku} — ${p.name}` })));

const initialItems = (props.prescription?.items || []).map((i) => ({
    product_id: i.product_id,
    quantity: Number(i.quantity),
    dosage: i.dosage || '',
    instructions: i.instructions || '',
}));

const form = useForm({
    id: props.prescription?.id || null,
    reference_number: props.prescription?.reference_number || '',
    branch_id: props.prescription?.branch_id || null,
    customer_id: props.prescription?.customer_id || null,
    prescription_date: props.prescription?.prescription_date || new Date().toISOString().substring(0, 10),
    doctor_name: props.prescription?.doctor_name || '',
    diagnosis: props.prescription?.diagnosis || '',
    notes: props.prescription?.notes || '',
    items: initialItems.length ? initialItems : [{ product_id: null, quantity: 1, dosage: '', instructions: '' }],
});

function addLine() {
    form.items.push({ product_id: null, quantity: 1, dosage: '', instructions: '' });
}

function submit() {
    if (form.id) form.put(route('admin.prescriptions.update', form.id));
    else form.post(route('admin.prescriptions.store'));
}
</script>
