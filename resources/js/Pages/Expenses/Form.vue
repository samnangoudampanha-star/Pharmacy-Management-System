<template>
    <div>
        <Head :title="form.id ? $t('common.edit') : $t('common.create')" />
        <div class="card">
            <div class="card-header bg-white d-flex">
                <h5 class="mb-0">{{ $t('menu.expenses') }}</h5>
                <Link :href="route('admin.expenses.index')" class="btn btn-outline-secondary btn-sm ms-auto">
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
                        <label class="form-label">{{ $t('fields.category') }} <span class="text-danger">*</span></label>
                        <TomSelectInput v-model="form.expense_category_id" :options="catOpts" :placeholder="$t('common.select')" />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ $t('fields.expense_date') }} <span class="text-danger">*</span></label>
                        <FlatpickrInput v-model="form.expense_date" date-format="Y-m-d" />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ $t('fields.title') }} <span class="text-danger">*</span></label>
                        <input v-model="form.title" class="form-control" :class="{ 'is-invalid': form.errors.title }" required />
                        <div class="invalid-feedback">{{ form.errors.title }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ $t('fields.amount') }} <span class="text-danger">*</span></label>
                        <input v-model.number="form.amount" type="number" step="0.01" class="form-control" required />
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ $t('fields.notes') }}</label>
                        <textarea v-model="form.notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-primary" :disabled="form.processing">{{ $t('common.save') }}</button>
                    <Link :href="route('admin.expenses.index')" class="btn btn-outline-secondary">{{ $t('common.cancel') }}</Link>
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
    expense: { type: Object, default: null },
    options: { type: Object, required: true },
});

const branchOpts = computed(() => props.options.branches.map((b) => ({ value: b.id, label: b.name })));
const catOpts = computed(() => props.options.categories.map((c) => ({ value: c.id, label: c.name })));

const form = useForm({
    id: props.expense?.id || null,
    reference_number: props.expense?.reference_number || '',
    branch_id: props.expense?.branch_id || null,
    expense_category_id: props.expense?.expense_category_id || null,
    expense_date: props.expense?.expense_date || new Date().toISOString().substring(0, 10),
    title: props.expense?.title || '',
    amount: Number(props.expense?.amount || 0),
    notes: props.expense?.notes || '',
});

function submit() {
    if (form.id) form.put(route('admin.expenses.update', form.id));
    else form.post(route('admin.expenses.store'));
}
</script>
