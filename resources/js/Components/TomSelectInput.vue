<template>
    <select ref="selectRef" :multiple="multiple" class="form-select"></select>
</template>

<script setup>
import { onMounted, onBeforeUnmount, ref, watch } from 'vue';
import TomSelect from 'tom-select';

const props = defineProps({
    modelValue: { type: [String, Number, Array, null], default: null },
    options: { type: Array, default: () => [] },
    labelField: { type: String, default: 'label' },
    valueField: { type: String, default: 'value' },
    searchField: { type: Array, default: () => ['label'] },
    placeholder: { type: String, default: '' },
    multiple: { type: Boolean, default: false },
    allowEmptyOption: { type: Boolean, default: true },
});
const emit = defineEmits(['update:modelValue']);

const selectRef = ref(null);
let ts;

function rebuild() {
    if (!ts) return;
    ts.clearOptions();
    if (props.allowEmptyOption && !props.multiple) {
        ts.addOption({ [props.valueField]: '', [props.labelField]: props.placeholder || ' ' });
    }
    props.options.forEach((opt) => ts.addOption(opt));
    ts.refreshOptions(false);
    const value = props.modelValue == null ? '' : props.modelValue;
    ts.setValue(value, true);
}

onMounted(() => {
    ts = new TomSelect(selectRef.value, {
        valueField: props.valueField,
        labelField: props.labelField,
        searchField: props.searchField,
        placeholder: props.placeholder,
        plugins: props.multiple ? ['remove_button'] : [],
        allowEmptyOption: props.allowEmptyOption,
        maxOptions: 200,
        onChange: (value) => emit('update:modelValue', value),
    });
    rebuild();
});

watch(() => props.options, rebuild, { deep: true });
watch(
    () => props.modelValue,
    (value) => {
        if (ts && String(value ?? '') !== String(ts.getValue() ?? '')) {
            ts.setValue(value ?? '', true);
        }
    }
);

onBeforeUnmount(() => {
    if (ts) ts.destroy();
});
</script>
