<template>
  <Link
    :href="href"
    :class="[
      'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors touch-target',
      active
        ? 'bg-spiritual-100 dark:bg-spiritual-900 text-spiritual-700 dark:text-spiritual-300'
        : 'text-gray-700 dark:text-gray-300 hover:bg-cream-100 dark:hover:bg-gray-800'
    ]"
  >
    <slot />
  </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  href: {
    type: String,
    required: true,
  },
  active: {
    type: Boolean,
    default: false,
  },
});
</script>
