<template>
    <div>
        <Head :title="$t('branches.title')" />
        <DataTable :title="$t('branches.title')" :ajax-url="ajax_url" :columns="columns">
            <template #actions>
                <Link :href="route('admin.branches.create')" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>{{ $t('branches.create') }}
                </Link>
            </template>
        </DataTable>
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import DataTable from '@/Components/DataTable.vue';
import { trans } from '@/i18n';
import { installDataTableActions, renderActions, renderBoolean } from '@/datatable-helpers';

defineProps({ ajax_url: { type: String, required: true } });

const columns = [
    { data: 'id', title: trans('fields.id') },
    { data: 'code', title: trans('fields.code') },
    { data: 'name', title: trans('fields.name') },
    { data: 'phone', title: trans('fields.phone') },
    { data: 'email', title: trans('fields.email') },
    { data: 'city', title: trans('fields.city') },
    {
        data: 'is_main',
        title: trans('branches.is_main'),
        render: (v) => renderBoolean(v, trans('common.yes'), trans('common.no')),
    },
    { data: 'is_active', title: trans('common.status'), render: renderBoolean },
    { data: 'actions', title: trans('common.actions'), orderable: false, searchable: false, render: renderActions },
];

onMounted(installDataTableActions);
</script>
