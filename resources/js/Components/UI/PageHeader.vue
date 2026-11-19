<template>
  <div class="mb-6 sm:mb-8">
    <p
      v-if="eyebrow"
      class="text-xs font-semibold uppercase tracking-[0.18em] text-gold-700 dark:text-gold-300"
    >
      {{ eyebrow }}
    </p>

    <div class="mt-2 flex flex-wrap items-start justify-between gap-4">
      <div class="min-w-0">
        <h1 class="font-display text-3xl text-gray-900 dark:text-white sm:text-4xl">
          {{ title }}
        </h1>
        <p v-if="description" class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400 sm:text-base">
          {{ description }}
        </p>
      </div>

      <div v-if="$slots.actions" class="flex flex-wrap items-center gap-2 sm:gap-3">
        <slot name="actions" />
      </div>
    </div>

    <div v-if="$slots.tabs" class="mt-6 border-b border-cream-300 dark:border-gray-700">
      <slot name="tabs" />
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    required: true,
  },
  description: String,
  eyebrow: String,
});
</script>
