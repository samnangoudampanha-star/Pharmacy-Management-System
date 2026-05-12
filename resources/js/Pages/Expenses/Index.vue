<template>
    <div>
        <Head :title="$t('menu.expenses')" />
        <DataTable :title="$t('menu.expenses')" :ajax-url="ajax_url" :columns="columns">
            <template #actions>
                <Link :href="route('admin.expenses.create')" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>{{ $t('common.new') }}
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
    { data: 'reference_number', title: trans('fields.reference_number') },
    { data: 'branch_name', title: trans('fields.branch') },
    { data: 'category_name', title: trans('fields.category') },
    { data: 'title', title: trans('fields.title') },
    { data: 'amount', title: trans('fields.amount'), render: renderMoney, className: 'text-end' },
    { data: 'expense_date', title: trans('fields.expense_date') },
    { data: 'actions', title: trans('common.actions'), orderable: false, searchable: false, render: renderActions },
];

onMounted(installDataTableActions);
</script>
