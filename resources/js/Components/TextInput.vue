<template>
    <input
        :id="id"
        ref="input"
        :type="type"
        :value="modelValue"
        :required="required"
        :placeholder="placeholder"
        @input="$emit('update:modelValue', $event.target.value)"
        class="border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm w-full"
    />
</template>

<script setup>
import { ref, onMounted } from 'vue';

defineProps({
    id: String,
    type: {
        type: String,
        default: 'text',
    },
    modelValue: String,
    required: Boolean,
    placeholder: String,
});

defineEmits(['update:modelValue']);

const input = ref(null);

defineExpose({ focus: () => input.value.focus() });

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});
</script>
