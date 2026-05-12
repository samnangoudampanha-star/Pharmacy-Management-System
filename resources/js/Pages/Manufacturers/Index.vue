<template>
    <div>
        <Head :title="$t('menu.manufacturers')" />
        <DataTable :title="$t('menu.manufacturers')" :ajax-url="ajax_url" :columns="columns">
            <template #actions>
                <Link :href="route('admin.manufacturers.create')" class="btn btn-primary">
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
import { installDataTableActions, renderActions, renderBoolean } from '@/datatable-helpers';

defineProps({ ajax_url: { type: String, required: true } });

const columns = [
    { data: 'id', title: trans('fields.id') },
    { data: 'name', title: trans('fields.name') },
    { data: 'country', title: trans('fields.country') },
    { data: 'phone', title: trans('fields.phone') },
    { data: 'email', title: trans('fields.email') },
    { data: 'is_active', title: trans('common.status'), render: renderBoolean },
    { data: 'actions', title: trans('common.actions'), orderable: false, searchable: false, render: renderActions },
];

onMounted(installDataTableActions);
</script>
