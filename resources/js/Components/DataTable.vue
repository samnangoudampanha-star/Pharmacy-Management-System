<template>
    <div class="card">
        <div class="card-header bg-white d-flex flex-wrap gap-2 align-items-center">
            <h5 class="mb-0 me-auto">
                <slot name="title">{{ title }}</slot>
            </h5>
            <slot name="actions" />
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table ref="tableRef" class="table table-hover align-middle w-100">
                    <thead>
                        <tr>
                            <th v-for="col in columns" :key="col.data || col.name">
                                {{ col.title }}
                            </th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, onBeforeUnmount, ref, watch } from 'vue';
import $ from 'jquery';
import 'datatables.net-bs5';
import 'datatables.net-buttons-bs5';

const props = defineProps({
    title: { type: String, default: '' },
    ajaxUrl: { type: String, required: true },
    columns: { type: Array, required: true },
    order: { type: Array, default: () => [[0, 'desc']] },
    pageLength: { type: Number, default: 10 },
    refreshKey: { type: [Number, String], default: 0 },
});

const tableRef = ref(null);
let dt;

function getCsrfToken() {
    const el = document.head.querySelector('meta[name="csrf-token"]');
    return el ? el.getAttribute('content') : '';
}

function initTable() {
    dt = $(tableRef.value).DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: props.ajaxUrl,
            type: 'GET',
            headers: { 'X-CSRF-TOKEN': getCsrfToken() },
        },
        columns: props.columns,
        order: props.order,
        pageLength: props.pageLength,
        lengthMenu: [
            [10, 25, 50, 100],
            [10, 25, 50, 100],
        ],
        responsive: true,
        // Use Bootstrap 5 pagination layout
        dom:
            "<'row mb-3'<'col-sm-6'l><'col-sm-6'f>>" +
            "<'row'<'col-12'tr>>" +
            "<'row mt-3'<'col-sm-6'i><'col-sm-6'p>>",
        pagingType: 'full_numbers',
        language: {
            search: '',
            searchPlaceholder: 'Search...',
            lengthMenu: '_MENU_',
            info: 'Showing _START_ to _END_ of _TOTAL_ records',
            infoEmpty: 'Showing 0 records',
            paginate: {
                first: '«',
                previous: '‹',
                next: '›',
                last: '»',
            },
        },
    });
}

function reloadHandler() {
    if (dt) dt.ajax.reload(null, false);
}

onMounted(() => {
    initTable();
    document.addEventListener('datatables:reload', reloadHandler);
});

onBeforeUnmount(() => {
    document.removeEventListener('datatables:reload', reloadHandler);
    if (dt) {
        dt.destroy();
        dt = null;
    }
});

watch(
    () => props.refreshKey,
    () => {
        if (dt) dt.ajax.reload(null, false);
    }
);

defineExpose({
    reload: () => dt && dt.ajax.reload(null, false),
});
</script>
