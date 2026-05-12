<template>
    <input ref="inputRef" :value="modelValue" v-bind="$attrs" class="form-control" />
</template>

<script setup>
import { onMounted, onBeforeUnmount, ref, watch } from 'vue';
import flatpickr from 'flatpickr';

const props = defineProps({
    modelValue: { type: [String, Number, Date], default: '' },
    enableTime: { type: Boolean, default: false },
    dateFormat: { type: String, default: 'Y-m-d' },
});
const emit = defineEmits(['update:modelValue']);

const inputRef = ref(null);
let instance;

onMounted(() => {
    instance = flatpickr(inputRef.value, {
        enableTime: props.enableTime,
        dateFormat: props.dateFormat,
        defaultDate: props.modelValue || null,
        onChange: (_dates, str) => emit('update:modelValue', str),
    });
});

watch(
    () => props.modelValue,
    (value) => {
        if (instance && value !== instance.input.value) {
            instance.setDate(value, false);
        }
    }
);

onBeforeUnmount(() => {
    if (instance) instance.destroy();
});
</script>
