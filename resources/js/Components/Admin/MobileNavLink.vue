<template>
  <Link
    :href="href"
    :class="[
      'flex flex-col items-center justify-center flex-1 h-full text-xs touch-target',
      active
        ? 'text-spiritual-700 dark:text-spiritual-400'
        : 'text-gray-500 dark:text-gray-400'
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
