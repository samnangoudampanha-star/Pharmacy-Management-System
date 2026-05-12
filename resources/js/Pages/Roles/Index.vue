<template>
    <div>
        <Head :title="$t('roles.title')" />
        <DataTable
            :title="$t('roles.title')"
            :ajax-url="ajax_url"
            :columns="columns"
        >
            <template #actions>
                <Link
                    :href="route('admin.roles.create')"
                    class="btn btn-primary"
                >
                    <i class="bi bi-plus-lg me-1"></i>{{ $t("roles.create") }}
                </Link>
            </template>
        </DataTable>
    </div>
</template>

<script setup>
import { onMounted } from "vue";
import DataTable from "@/Components/DataTable.vue";
import { trans } from "@/i18n";
import { installDataTableActions, renderActions } from "@/datatable-helpers";

defineProps({ ajax_url: { type: String, required: true } });

const columns = [
    { data: "id", title: trans("fields.id") },
    { data: "name", title: trans("fields.name") },
    { data: "display_name", title: trans("fields.display_name") },
    { data: "description", title: trans("fields.description") },
    { data: "permissions_count", title: trans("fields.permissions") },
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
