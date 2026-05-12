<template>
    <div v-if="flash.success || flash.error || flash.warning || flash.info" class="mb-3">
        <div v-if="flash.success" class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>{{ flash.success }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <div v-if="flash.error" class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ flash.error }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <div v-if="flash.warning" class="alert alert-warning alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ flash.warning }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <div v-if="flash.info" class="alert alert-info alert-dismissible fade show">
            <i class="bi bi-info-circle me-2"></i>{{ flash.info }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
</template>

<script setup>
import { computed, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const page = usePage();
const flash = computed(() => page.props.flash || {});

watch(
    () => flash.value,
    (value) => {
        if (!value) return;
        if (value.toast?.length) {
            value.toast.forEach((msg) => {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: msg.type || 'success',
                    title: msg.text,
                    showConfirmButton: false,
                    timer: 3500,
                    timerProgressBar: true,
                });
            });
        }
    },
    { deep: true }
);
</script>
