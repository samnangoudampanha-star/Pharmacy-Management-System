<template>
    <div>
        <Head :title="$t('menu.stock_transfers')" />
        <DataTable :title="$t('menu.stock_transfers')" :ajax-url="ajax_url" :columns="columns">
            <template #actions>
                <Link :href="route('admin.stock-transfers.create')" class="btn btn-primary">
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
import { installDataTableActions, renderActions } from '@/datatable-helpers';

defineProps({ ajax_url: { type: String, required: true } });

const columns = [
    { data: 'id', title: trans('fields.id') },
    { data: 'reference_number', title: trans('fields.reference_number') },
    { data: 'from_branch_name', title: 'From' },
    { data: 'to_branch_name', title: 'To' },
    { data: 'transfer_date', title: 'Date' },
    { data: 'status', title: trans('common.status') },
    { data: 'actions', title: trans('common.actions'), orderable: false, searchable: false, render: renderActions },
];

onMounted(installDataTableActions);
</script>
