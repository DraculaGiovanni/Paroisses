<template>
  <Link
    :href="href"
    :class="[
      'flex items-center gap-3 rounded-r-lg py-2.5 pl-3 pr-2 text-sm font-medium transition-colors touch-target',
      active
        ? 'border-l-2 border-gold-500 accent-soft accent-text dark:bg-gray-800'
        : 'border-l-2 border-transparent text-gray-700 hover:bg-cream-100 dark:text-gray-300 dark:hover:bg-gray-800'
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
