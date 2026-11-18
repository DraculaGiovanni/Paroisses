<template>
  <div class="text-center py-12">
    <div class="mx-auto h-24 w-24 text-cream-400 dark:text-gray-600 mb-4">
      <slot name="icon">
        <svg class="w-full h-full" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
        </svg>
      </slot>
    </div>
    
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
      {{ title }}
    </h3>
    
    <p class="text-gray-600 dark:text-gray-400 max-w-md mx-auto mb-6">
      {{ description }}
    </p>
    
    <div v-if="$slots.action">
      <slot name="action" />
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    default: 'Aucune donnée',
  },
  description: {
    type: String,
    default: 'Aucun élément à afficher pour le moment.',
  },
});
</script>
