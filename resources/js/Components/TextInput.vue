<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="min-h-11 w-full rounded-xl border-0 px-4 py-2.5 shadow-sm ring-1 ring-inset ring-cream-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-spiritual-600 dark:bg-gray-900 dark:text-white dark:ring-gray-700"
        v-model="model"
        ref="input"
    />
</template>
