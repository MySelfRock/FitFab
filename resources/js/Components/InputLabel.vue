<template>
    <label :for="for" :class="['block font-medium text-sm text-gray-700', value ? '' : '']">
        <span v-if="value">{{ value }}</span>
        <slot v-else />
    </label>
</template>

<script setup>
defineProps({
    for: String,
    value: String,
});
</script>
