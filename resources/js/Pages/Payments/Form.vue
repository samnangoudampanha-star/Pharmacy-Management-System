<template>
    <div>
        <Head :title="form.id ? $t('payments.edit') : $t('payments.create')" />
        <div class="card">
            <div class="card-header bg-white d-flex align-items-center">
                <h5 class="mb-0">
                    {{ form.id ? $t("payments.edit") : $t("payments.create") }}
                </h5>
                <Link
                    :href="route('admin.payments.index')"
                    class="btn btn-outline-secondary btn-sm ms-auto"
                >
                    <i class="bi bi-arrow-left me-1"></i>{{ $t("common.back") }}
                </Link>
            </div>
            <form class="card-body" @submit.prevent="submit">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">{{
                            $t("fields.reference_number")
                        }}</label>
                        <input
                            v-model="form.reference_number"
                            class="form-control"
                            :class="{
                                'is-invalid': form.errors.reference_number,
                            }"
                        />
                        <div class="invalid-feedback">
                            {{ form.errors.reference_number }}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"
                            >{{ $t("fields.branch") }}
                            <span class="text-danger">*</span></label
                        >
                        <TomSelectInput
                            v-model="form.branch_id"
                            :options="branchOptions"
                            :placeholder="$t('common.select')"
                        />
                        <div
                            v-if="form.errors.branch_id"
                            class="text-danger small mt-1"
                        >
                            {{ form.errors.branch_id }}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"
                            >{{ $t("fields.payment_type") }}
                            <span class="text-danger">*</span></label
                        >
                        <TomSelectInput
                            v-model="form.payable_type"
                            :options="typeOptions"
                            :placeholder="$t('common.select')"
                        />
                        <div
                            v-if="form.errors.payable_type"
                            class="text-danger small mt-1"
                        >
                            {{ form.errors.payable_type }}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"
                            >{{ $t("fields.payment_for") }}
                            <span class="text-danger">*</span></label
                        >
                        <TomSelectInput
                            v-model="form.payable_id"
                            :options="payableOptions"
                            :placeholder="$t('common.select')"
                        />
                        <div
                            v-if="form.errors.payable_id"
                            class="text-danger small mt-1"
                        >
                            {{ form.errors.payable_id }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label"
                            >{{ $t("fields.payment_date") }}
                            <span class="text-danger">*</span></label
                        >
                        <FlatpickrInput
                            v-model="form.payment_date"
                            date-format="Y-m-d"
                        />
                        <div
                            v-if="form.errors.payment_date"
                            class="text-danger small mt-1"
                        >
                            {{ form.errors.payment_date }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label"
                            >{{ $t("fields.amount") }}
                            <span class="text-danger">*</span></label
                        >
                        <input
                            v-model.number="form.amount"
                            type="number"
                            step="0.01"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.amount }"
                        />
                        <div class="invalid-feedback">
                            {{ form.errors.amount }}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"
                            >{{ $t("fields.payment_method") }}
                            <span class="text-danger">*</span></label
                        >
                        <TomSelectInput
                            v-model="form.method"
                            :options="methodOptions"
                            :placeholder="$t('common.select')"
                        />
                        <div
                            v-if="form.errors.method"
                            class="text-danger small mt-1"
                        >
                            {{ form.errors.method }}
                        </div>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">{{
                            $t("fields.notes")
                        }}</label>
                        <input
                            v-model="form.notes"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.notes }"
                        />
                        <div class="invalid-feedback">
                            {{ form.errors.notes }}
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-primary" :disabled="form.processing">
                        {{ $t("common.save") }}
                    </button>
                    <Link
                        :href="route('admin.payments.index')"
                        class="btn btn-outline-secondary"
                        >{{ $t("common.cancel") }}</Link
                    >
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { computed, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import FlatpickrInput from "@/Components/FlatpickrInput.vue";
import TomSelectInput from "@/Components/TomSelectInput.vue";

const props = defineProps({
    payment: { type: Object, default: null },
    options: { type: Object, required: true },
});

const branchOptions = computed(() =>
    props.options.branches.map((branch) => ({
        value: branch.id,
        label: branch.name,
    })),
);
const typeOptions = computed(() =>
    props.options.payable_types.map((type) => ({
        value: type.value,
        label: type.label,
    })),
);
const methodOptions = computed(() =>
    props.options.methods.map((method) => ({ value: method, label: method })),
);
const payableOptions = computed(() =>
    (props.options.payables[form.payable_type] || []).map((payable) => ({
        value: payable.id,
        label: payable.label,
    })),
);

const form = useForm({
    id: props.payment?.id || null,
    reference_number: props.payment?.reference_number || "",
    branch_id: props.payment?.branch_id || null,
    payable_type: props.payment?.payable_type || "sale",
    payable_id: props.payment?.payable_id || null,
    payment_date:
        props.payment?.payment_date ||
        new Date().toISOString().substring(0, 10),
    amount: Number(props.payment?.amount || 0),
    method: props.payment?.method || "cash",
    notes: props.payment?.notes || "",
});

watch(
    () => form.payable_type,
    () => {
        form.payable_id = null;
    },
);

function submit() {
    if (form.id) form.put(route("admin.payments.update", form.id));
    else form.post(route("admin.payments.store"));
}
</script>
