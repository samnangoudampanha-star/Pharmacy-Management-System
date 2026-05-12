<template>
    <div>
        <Head :title="$t('payments.title')" />
        <DataTable
            :title="$t('payments.title')"
            :ajax-url="ajax_url"
            :columns="columns"
        >
            <template #actions>
                <Link
                    :href="route('admin.payments.create')"
                    class="btn btn-primary"
                >
                    <i class="bi bi-plus-lg me-1"></i
                    >{{ $t("payments.create") }}
                </Link>
            </template>
        </DataTable>
    </div>
</template>

<script setup>
import { onMounted } from "vue";
import DataTable from "@/Components/DataTable.vue";
import { trans } from "@/i18n";
import {
    installDataTableActions,
    renderActions,
    renderMoney,
} from "@/datatable-helpers";

defineProps({ ajax_url: { type: String, required: true } });

const columns = [
    { data: "id", title: trans("fields.id") },
    { data: "reference_number", title: trans("fields.reference_number") },
    { data: "branch_name", title: trans("fields.branch") },
    { data: "payable_type_label", title: trans("fields.payment_type") },
    { data: "payable_label", title: trans("fields.payment_for") },
    { data: "payment_date", title: trans("fields.payment_date") },
    { data: "method", title: trans("fields.payment_method") },
    { data: "amount", title: trans("fields.amount"), render: renderMoney },
    {
        data: "actions",
        title: trans("common.actions"),
        orderable: false,
        searchable: false,
        render: renderActions,
    },
];

onMounted(installDataTableActions);
</script>
