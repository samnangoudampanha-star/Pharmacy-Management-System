<template>
    <div>
        <Head :title="$t('sales.title')" />
        <DataTable :title="$t('sales.title')" :ajax-url="ajax_url" :columns="columns">
            <template #actions>
                <Link :href="route('admin.sales.create')" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>{{ $t('sales.create') }}
                </Link>
            </template>
        </DataTable>
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import DataTable from '@/Components/DataTable.vue';
import { trans } from '@/i18n';
import { installDataTableActions, renderActions, renderMoney } from '@/datatable-helpers';

defineProps({ ajax_url: { type: String, required: true } });

const columns = [
    { data: 'id', title: trans('fields.id') },
    { data: 'invoice_number', title: trans('fields.invoice_number') },
    { data: 'branch_name', title: trans('fields.branch') },
    { data: 'customer_name', title: trans('fields.customer') },
    { data: 'sale_date', title: 'Date' },
    { data: 'subtotal', title: trans('fields.subtotal'), render: renderMoney, className: 'text-end' },
    { data: 'tax', title: trans('fields.tax'), render: renderMoney, className: 'text-end' },
    { data: 'total', title: trans('fields.total'), render: renderMoney, className: 'text-end' },
    { data: 'paid', title: trans('fields.paid'), render: renderMoney, className: 'text-end' },
    { data: 'due', title: trans('fields.due'), render: renderMoney, className: 'text-end' },
    { data: 'status', title: trans('common.status') },
    { data: 'actions', title: trans('common.actions'), orderable: false, searchable: false, render: renderActions },
];

onMounted(installDataTableActions);
</script>
