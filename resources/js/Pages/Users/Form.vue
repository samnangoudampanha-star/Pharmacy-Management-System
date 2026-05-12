<template>
    <div>
        <Head :title="form.id ? $t('common.edit') : $t('common.create')" />
        <div class="card">
            <div class="card-header bg-white d-flex">
                <h5 class="mb-0">{{ $t('menu.users') }}</h5>
                <Link :href="route('admin.users.index')" class="btn btn-outline-secondary btn-sm ms-auto">
                    <i class="bi bi-arrow-left me-1"></i>{{ $t('common.back') }}
                </Link>
            </div>
            <form class="card-body" @submit.prevent="submit">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ $t('fields.name') }} <span class="text-danger">*</span></label>
                        <input v-model="form.name" class="form-control" :class="{ 'is-invalid': form.errors.name }" required />
                        <div class="invalid-feedback">{{ form.errors.name }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ $t('fields.email') }} <span class="text-danger">*</span></label>
                        <input v-model="form.email" type="email" class="form-control" :class="{ 'is-invalid': form.errors.email }" required />
                        <div class="invalid-feedback">{{ form.errors.email }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ $t('fields.password') }} <span v-if="!form.id" class="text-danger">*</span></label>
                        <input v-model="form.password" type="password" class="form-control" :class="{ 'is-invalid': form.errors.password }" :required="!form.id" />
                        <div class="invalid-feedback">{{ form.errors.password }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirm Password</label>
                        <input v-model="form.password_confirmation" type="password" class="form-control" />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ $t('fields.phone') }}</label>
                        <input v-model="form.phone" class="form-control" />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ $t('fields.branch') }}</label>
                        <TomSelectInput
                            v-model="form.branch_id"
                            :options="branchOptions"
                            :placeholder="$t('common.select')"
                        />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Role</label>
                        <TomSelectInput
                            v-model="form.role_id"
                            :options="roleOptions"
                            :placeholder="$t('common.select')"
                        />
                    </div>
                    <div class="col-12"><label class="form-label">{{ $t('fields.address') }}</label><input v-model="form.address" class="form-control" /></div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input id="active" v-model="form.is_active" type="checkbox" class="form-check-input" />
                            <label for="active" class="form-check-label">{{ $t('common.active') }}</label>
                        </div>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-primary" :disabled="form.processing">{{ $t('common.save') }}</button>
                    <Link :href="route('admin.users.index')" class="btn btn-outline-secondary">{{ $t('common.cancel') }}</Link>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import TomSelectInput from '@/Components/TomSelectInput.vue';

const props = defineProps({
    user: { type: Object, default: null },
    options: { type: Object, required: true },
});

const branchOptions = computed(() => props.options.branches.map((b) => ({ value: b.id, label: b.name })));
const roleOptions = computed(() => props.options.roles.map((r) => ({ value: r.id, label: r.display_name || r.name })));

const form = useForm({
    id: props.user?.id || null,
    name: props.user?.name || '',
    email: props.user?.email || '',
    password: '',
    password_confirmation: '',
    phone: props.user?.phone || '',
    address: props.user?.address || '',
    branch_id: props.user?.branch_id || null,
    role_id: props.user?.role_id || null,
    is_active: props.user?.is_active ?? true,
});

function submit() {
    if (form.id) form.put(route('admin.users.update', form.id));
    else form.post(route('admin.users.store'));
}
</script>
