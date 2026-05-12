<template>
    <div>
        <Head :title="$t('products.title')" />
        <DataTable :title="$t('products.title')" :ajax-url="ajax_url" :columns="columns">
            <template #actions>
                <Link :href="route('admin.products.create')" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>{{ $t('products.create') }}
                </Link>
            </template>
        </DataTable>
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import DataTable from '@/Components/DataTable.vue';
import { trans } from '@/i18n';
import { installDataTableActions, renderActions, renderBoolean, renderMoney } from '@/datatable-helpers';

defineProps({ ajax_url: { type: String, required: true } });

const columns = [
    { data: 'id', title: trans('fields.id') },
    { data: 'sku', title: trans('fields.sku') },
    { data: 'name', title: trans('fields.name') },
    { data: 'category_name', title: trans('fields.category') },
    { data: 'unit_name', title: trans('fields.unit') },
    { data: 'manufacturer_name', title: trans('fields.manufacturer') },
    { data: 'cost_price', title: trans('fields.cost_price'), render: renderMoney, className: 'text-end' },
    { data: 'sale_price', title: trans('fields.sale_price'), render: renderMoney, className: 'text-end' },
    { data: 'stock_on_hand', title: trans('fields.quantity'), render: (v) => Number(v || 0).toLocaleString(), className: 'text-end' },
    { data: 'is_active', title: trans('common.status'), render: renderBoolean },
    { data: 'actions', title: trans('common.actions'), orderable: false, searchable: false, render: renderActions },
];

onMounted(installDataTableActions);
</script>
