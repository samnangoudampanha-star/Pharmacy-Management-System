<template>
    <div>
        <Head :title="$t('menu.prescriptions')" />
        <DataTable :title="$t('menu.prescriptions')" :ajax-url="ajax_url" :columns="columns">
            <template #actions>
                <Link :href="route('admin.prescriptions.create')" class="btn btn-primary">
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
    { data: 'branch_name', title: trans('fields.branch') },
    { data: 'customer_name', title: trans('fields.customer') },
    { data: 'doctor_name', title: 'Doctor' },
    { data: 'prescription_date', title: 'Date' },
    { data: 'actions', title: trans('common.actions'), orderable: false, searchable: false, render: renderActions },
];

onMounted(installDataTableActions);
</script>
