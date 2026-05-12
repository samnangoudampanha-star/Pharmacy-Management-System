<template>
    <div>
        <Head :title="form.id ? $t('sales.edit') : $t('sales.create')" />
        <div class="card">
            <div class="card-header bg-white d-flex">
                <h5 class="mb-0">
                    {{ form.id ? $t("sales.edit") : $t("sales.create") }}
                </h5>
                <Link
                    :href="route('admin.sales.index')"
                    class="btn btn-outline-secondary btn-sm ms-auto"
                >
                    <i class="bi bi-arrow-left me-1"></i>{{ $t("common.back") }}
                </Link>
            </div>
            <form class="card-body" @submit.prevent="submit">
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label">{{
                            $t("fields.invoice_number")
                        }}</label>
                        <input
                            v-model="form.invoice_number"
                            class="form-control"
                        />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label"
                            >{{ $t("fields.branch") }}
                            <span class="text-danger">*</span></label
                        >
                        <TomSelectInput
                            v-model="form.branch_id"
                            :options="branchOpts"
                            :placeholder="$t('common.select')"
                        />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{
                            $t("fields.customer")
                        }}</label>
                        <TomSelectInput
                            v-model="form.customer_id"
                            :options="customerOpts"
                            :placeholder="$t('sales.walkin')"
                        />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label"
                            >Date <span class="text-danger">*</span></label
                        >
                        <FlatpickrInput
                            v-model="form.sale_date"
                            date-format="Y-m-d"
                        />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{
                            $t("fields.payment_method")
                        }}</label>
                        <TomSelectInput
                            v-model="form.payment_method"
                            :options="paymentMethodOpts"
                        />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{
                            $t("fields.paid")
                        }}</label>
                        <input
                            v-model.number="form.paid"
                            type="number"
                            step="0.01"
                            class="form-control"
                        />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{
                            $t("common.status")
                        }}</label>
                        <TomSelectInput
                            v-model="form.status"
                            :options="statusOpts"
                            :placeholder="$t('common.select')"
                        />
                    </div>
                </div>

                <div v-if="form.errors.items" class="alert alert-danger py-2">
                    {{ form.errors.items }}
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="min-width: 220px">
                                    {{ $t("fields.product") }}
                                </th>
                                <th class="text-end" style="width: 110px">
                                    {{ $t("fields.quantity") }}
                                </th>
                                <th class="text-end" style="width: 130px">
                                    {{ $t("fields.sale_price") }}
                                </th>
                                <th class="text-end" style="width: 100px">
                                    Tax %
                                </th>
                                <th class="text-end" style="width: 120px">
                                    {{ $t("fields.discount") }}
                                </th>
                                <th class="text-end" style="width: 140px">
                                    {{ $t("fields.subtotal") }}
                                </th>
                                <th style="width: 50px"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, idx) in form.items" :key="idx">
                                <td>
                                    <TomSelectInput
                                        v-model="item.product_id"
                                        :options="productOpts"
                                        :placeholder="$t('fields.product')"
                                        @update:modelValue="onPick(idx)"
                                    />
                                </td>
                                <td>
                                    <input
                                        v-model.number="item.quantity"
                                        type="number"
                                        step="0.0001"
                                        class="form-control text-end"
                                    />
                                </td>
                                <td>
                                    <input
                                        v-model.number="item.sale_price"
                                        type="number"
                                        step="0.0001"
                                        class="form-control text-end"
                                    />
                                </td>
                                <td>
                                    <input
                                        v-model.number="item.tax_rate"
                                        type="number"
                                        step="0.01"
                                        class="form-control text-end"
                                    />
                                </td>
                                <td>
                                    <input
                                        v-model.number="item.discount"
                                        type="number"
                                        step="0.01"
                                        class="form-control text-end"
                                    />
                                </td>
                                <td class="text-end">
                                    {{ format(lineTotal(item)) }}
                                </td>
                                <td>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-danger"
                                        @click="form.items.splice(idx, 1)"
                                    >
                                        <i class="bi bi-x"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!form.items.length">
                                <td
                                    colspan="7"
                                    class="text-center text-muted py-3"
                                >
                                    {{ $t("common.no_data") }}
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="7">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-primary"
                                        @click="addLine"
                                    >
                                        <i class="bi bi-plus me-1"></i>Add line
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-end fw-semibold">
                                    {{ $t("fields.subtotal") }}
                                </td>
                                <td class="text-end">
                                    {{ format(totals.subtotal) }}
                                </td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-end fw-semibold">
                                    {{ $t("fields.tax") }}
                                </td>
                                <td class="text-end">
                                    {{ format(totals.tax) }}
                                </td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-end fw-bold">
                                    {{ $t("fields.total") }}
                                </td>
                                <td class="text-end fw-bold">
                                    {{ format(totals.total) }}
                                </td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-end fw-semibold">
                                    {{ $t("fields.due") }}
                                </td>
                                <td class="text-end fw-bold text-danger">
                                    {{
                                        format(
                                            Math.max(
                                                0,
                                                totals.total -
                                                    Number(form.paid || 0),
                                            ),
                                        )
                                    }}
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-12">
                        <label class="form-label">{{
                            $t("fields.notes")
                        }}</label>
                        <textarea
                            v-model="form.notes"
                            class="form-control"
                            rows="2"
                        ></textarea>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-primary" :disabled="form.processing">
                        {{ $t("common.save") }}
                    </button>
                    <Link
                        :href="route('admin.sales.index')"
                        class="btn btn-outline-secondary"
                        >{{ $t("common.cancel") }}</Link
                    >
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import FlatpickrInput from "@/Components/FlatpickrInput.vue";
import TomSelectInput from "@/Components/TomSelectInput.vue";

const props = defineProps({
    sale: { type: Object, default: null },
    options: { type: Object, required: true },
});

const branchOpts = computed(() =>
    props.options.branches.map((b) => ({ value: b.id, label: b.name })),
);
const customerOpts = computed(() =>
    props.options.customers.map((c) => ({ value: c.id, label: c.name })),
);
const productOpts = computed(() =>
    props.options.products.map((p) => ({
        value: p.id,
        label: `${p.sku} — ${p.name}`,
    })),
);
const paymentMethodOpts = (props.options.payment_methods || []).map((m) => ({
    value: m,
    label: m,
}));
const statusOpts = computed(() =>
    (props.options.statuses || []).map((status) => ({
        value: status,
        label: status.replaceAll("_", " "),
    })),
);

const initialItems = (props.sale?.items || []).map((i) => ({
    product_id: i.product_id,
    quantity: Number(i.quantity),
    sale_price: Number(i.sale_price),
    tax_rate: Number(i.tax_rate),
    discount: Number(i.discount),
}));

const form = useForm({
    id: props.sale?.id || null,
    invoice_number: props.sale?.invoice_number || "",
    branch_id: props.sale?.branch_id || null,
    customer_id: props.sale?.customer_id || null,
    sale_date:
        props.sale?.sale_date || new Date().toISOString().substring(0, 10),
    payment_method: props.sale?.payment_method || "cash",
    paid: Number(props.sale?.paid || 0),
    status: props.sale?.status || "completed",
    notes: props.sale?.notes || "",
    items: initialItems.length ? initialItems : [emptyLine()],
});

function emptyLine() {
    return {
        product_id: null,
        quantity: 1,
        sale_price: 0,
        tax_rate: 0,
        discount: 0,
    };
}

function addLine() {
    form.items.push(emptyLine());
}

function onPick(idx) {
    const line = form.items[idx];
    const product = props.options.products.find(
        (p) => p.id === line.product_id,
    );
    if (product) {
        line.sale_price = Number(product.sale_price || 0);
        line.tax_rate = Number(product.tax_rate || 0);
    }
}

function lineTotal(item) {
    const sub =
        Number(item.quantity || 0) * Number(item.sale_price || 0) -
        Number(item.discount || 0);
    const tax = sub * (Number(item.tax_rate || 0) / 100);
    return sub + tax;
}

const totals = computed(() => {
    let subtotal = 0,
        tax = 0;
    for (const it of form.items) {
        const sub =
            Number(it.quantity || 0) * Number(it.sale_price || 0) -
            Number(it.discount || 0);
        subtotal += sub;
        tax += sub * (Number(it.tax_rate || 0) / 100);
    }
    return { subtotal, tax, total: subtotal + tax };
});

function format(n) {
    return Number(n || 0).toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

function submit() {
    if (form.id) form.put(route("admin.sales.update", form.id));
    else form.post(route("admin.sales.store"));
}
</script>
