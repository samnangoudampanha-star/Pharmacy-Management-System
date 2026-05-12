<template>
    <div>
        <Head :title="$t('menu.stocks')" />
        <DataTable :title="$t('menu.stocks')" :ajax-url="ajax_url" :columns="columns" />
    </div>
</template>

<script setup>
import DataTable from '@/Components/DataTable.vue';
import { trans } from '@/i18n';

defineProps({
    ajax_url: { type: String, required: true },
    branches: { type: Array, default: () => [] },
});

const columns = [
    { data: 'id', title: trans('fields.id') },
    { data: 'product_sku', title: trans('fields.sku') },
    { data: 'product_name', title: trans('fields.product') },
    { data: 'branch_name', title: trans('fields.branch') },
    { data: 'quantity', title: trans('fields.quantity'), className: 'text-end' },
    { data: 'reorder_level', title: trans('fields.reorder_level'), className: 'text-end' },
    { data: 'avg_cost', title: trans('fields.cost_price'), className: 'text-end' },
    { data: 'updated_at', title: 'Updated' },
];
</script>
