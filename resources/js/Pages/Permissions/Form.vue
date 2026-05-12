<template>
    <div>
        <Head
            :title="form.id ? $t('permissions.edit') : $t('permissions.create')"
        />

        <div class="card">
            <div class="card-header bg-white d-flex align-items-center">
                <h5 class="mb-0">
                    {{
                        form.id
                            ? $t("permissions.edit")
                            : $t("permissions.create")
                    }}
                </h5>
                <Link
                    :href="route('admin.permissions.index')"
                    class="btn btn-outline-secondary btn-sm ms-auto"
                >
                    <i class="bi bi-arrow-left me-1"></i>{{ $t("common.back") }}
                </Link>
            </div>
            <form class="card-body" @submit.prevent="submit">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label"
                            >{{ $t("fields.name") }}
                            <span class="text-danger">*</span></label
                        >
                        <input
                            v-model="form.name"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.name }"
                            required
                        />
                        <div class="invalid-feedback">
                            {{ form.errors.name }}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{
                            $t("fields.display_name")
                        }}</label>
                        <input
                            v-model="form.display_name"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.display_name }"
                        />
                        <div class="invalid-feedback">
                            {{ form.errors.display_name }}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{
                            $t("fields.group")
                        }}</label>
                        <input
                            v-model="form.group"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.group }"
                        />
                        <div class="invalid-feedback">
                            {{ form.errors.group }}
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-primary" :disabled="form.processing">
                        {{ $t("common.save") }}
                    </button>
                    <Link
                        :href="route('admin.permissions.index')"
                        class="btn btn-outline-secondary"
                        >{{ $t("common.cancel") }}</Link
                    >
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { useForm } from "@inertiajs/vue3";

const props = defineProps({
    permission: { type: Object, default: null },
});

const form = useForm({
    id: props.permission?.id || null,
    name: props.permission?.name || "",
    display_name: props.permission?.display_name || "",
    group: props.permission?.group || "",
});

function submit() {
    if (form.id) form.put(route("admin.permissions.update", form.id));
    else form.post(route("admin.permissions.store"));
}
</script>
