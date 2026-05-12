<template>
    <div>
        <Head :title="form.id ? $t('common.edit') : $t('common.create')" />
        <div class="card">
            <div class="card-header bg-white d-flex">
                <h5 class="mb-0">{{ $t('menu.stock_transfers') }}</h5>
                <Link :href="route('admin.stock-transfers.index')" class="btn btn-outline-secondary btn-sm ms-auto">
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
                        <label class="form-label">From Branch <span class="text-danger">*</span></label>
                        <TomSelectInput v-model="form.from_branch_id" :options="branchOpts" :placeholder="$t('common.select')" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">To Branch <span class="text-danger">*</span></label>
                        <TomSelectInput v-model="form.to_branch_id" :options="branchOpts" :placeholder="$t('common.select')" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date <span class="text-danger">*</span></label>
                        <FlatpickrInput v-model="form.transfer_date" date-format="Y-m-d" />
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>{{ $t('fields.product') }}</th>
                                <th class="text-end" style="width: 160px;">{{ $t('fields.quantity') }}</th>
                                <th style="width: 50px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, idx) in form.items" :key="idx">
                                <td><TomSelectInput v-model="item.product_id" :options="productOpts" :placeholder="$t('fields.product')" /></td>
                                <td><input v-model.number="item.quantity" type="number" step="0.0001" class="form-control text-end" /></td>
                                <td><button type="button" class="btn btn-sm btn-outline-danger" @click="form.items.splice(idx, 1)"><i class="bi bi-x"></i></button></td>
                            </tr>
                            <tr v-if="!form.items.length"><td colspan="3" class="text-center text-muted py-3">{{ $t('common.no_data') }}</td></tr>
                        </tbody>
                        <tfoot>
                            <tr><td colspan="3"><button type="button" class="btn btn-sm btn-outline-primary" @click="addLine"><i class="bi bi-plus me-1"></i>Add line</button></td></tr>
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
                    <Link :href="route('admin.stock-transfers.index')" class="btn btn-outline-secondary">{{ $t('common.cancel') }}</Link>
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
    transfer: { type: Object, default: null },
    options: { type: Object, required: true },
});

const branchOpts = computed(() => props.options.branches.map((b) => ({ value: b.id, label: b.name })));
const productOpts = computed(() => props.options.products.map((p) => ({ value: p.id, label: `${p.sku} — ${p.name}` })));

const initialItems = (props.transfer?.items || []).map((i) => ({
    product_id: i.product_id, quantity: Number(i.quantity),
}));

const form = useForm({
    id: props.transfer?.id || null,
    reference_number: props.transfer?.reference_number || '',
    from_branch_id: props.transfer?.from_branch_id || null,
    to_branch_id: props.transfer?.to_branch_id || null,
    transfer_date: props.transfer?.transfer_date || new Date().toISOString().substring(0, 10),
    notes: props.transfer?.notes || '',
    items: initialItems.length ? initialItems : [{ product_id: null, quantity: 1 }],
});

function addLine() {
    form.items.push({ product_id: null, quantity: 1 });
}

function submit() {
    if (form.id) form.put(route('admin.stock-transfers.update', form.id));
    else form.post(route('admin.stock-transfers.store'));
}
</script>
