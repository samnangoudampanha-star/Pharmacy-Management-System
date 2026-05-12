<template>
    <button type="button" class="btn btn-sm btn-outline-danger" @click="confirmDelete">
        <i class="bi bi-trash"></i>
        <span v-if="label" class="ms-1">{{ label }}</span>
    </button>
</template>

<script setup>
import Swal from 'sweetalert2';
import { router } from '@inertiajs/vue3';
import { trans } from '@/i18n';

const props = defineProps({
    href: { type: String, required: true },
    label: { type: String, default: '' },
    confirmTitle: { type: String, default: '' },
    confirmText: { type: String, default: '' },
    onSuccess: { type: Function, default: null },
});

function confirmDelete() {
    Swal.fire({
        title: props.confirmTitle || trans('common.confirm_delete_title'),
        text: props.confirmText || trans('common.confirm_delete_text'),
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: trans('common.confirm_yes_delete'),
        cancelButtonText: trans('common.cancel'),
    }).then((result) => {
        if (!result.isConfirmed) return;
        router.delete(props.href, {
            preserveScroll: true,
            onSuccess: () => {
                if (props.onSuccess) props.onSuccess();
            },
        });
    });
}
</script>
