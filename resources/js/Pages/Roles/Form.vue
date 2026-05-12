<template>
    <div>
        <Head :title="form.id ? $t('roles.edit') : $t('roles.create')" />

        <div class="card">
            <div class="card-header bg-white d-flex align-items-center">
                <h5 class="mb-0">
                    {{ form.id ? $t("roles.edit") : $t("roles.create") }}
                </h5>
                <Link
                    :href="route('admin.roles.index')"
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
                            $t("fields.description")
                        }}</label>
                        <input
                            v-model="form.description"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.description }"
                        />
                        <div class="invalid-feedback">
                            {{ form.errors.description }}
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <h6 class="mb-3">{{ $t("fields.permissions") }}</h6>
                    <div class="row g-3">
                        <div
                            v-for="(permissions, group) in props.options
                                .permissions"
                            :key="group"
                            class="col-lg-4 col-md-6"
                        >
                            <div class="border rounded p-3 h-100">
                                <div
                                    class="fw-semibold text-uppercase small mb-2"
                                >
                                    {{ group }}
                                </div>
                                <div
                                    v-for="permission in permissions"
                                    :key="permission.id"
                                    class="form-check mb-2"
                                >
                                    <input
                                        :id="`permission-${permission.id}`"
                                        v-model="form.permission_ids"
                                        class="form-check-input"
                                        type="checkbox"
                                        :value="permission.id"
                                    />
                                    <label
                                        :for="`permission-${permission.id}`"
                                        class="form-check-label"
                                    >
                                        {{
                                            permission.display_name ||
                                            permission.name
                                        }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        v-if="form.errors.permission_ids"
                        class="text-danger small mt-2"
                    >
                        {{ form.errors.permission_ids }}
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-primary" :disabled="form.processing">
                        {{ $t("common.save") }}
                    </button>
                    <Link
                        :href="route('admin.roles.index')"
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
    role: { type: Object, default: null },
    options: { type: Object, required: true },
});

const form = useForm({
    id: props.role?.id || null,
    name: props.role?.name || "",
    display_name: props.role?.display_name || "",
    description: props.role?.description || "",
    permission_ids: props.role?.permission_ids || [],
});

function submit() {
    if (form.id) form.put(route("admin.roles.update", form.id));
    else form.post(route("admin.roles.store"));
}
</script>
