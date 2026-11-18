<template>
  <div :class="skeletonClasses" :style="customStyle" />
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  variant: {
    type: String,
    default: 'text',
    validator: (value) => ['text', 'circular', 'rectangular'].includes(value),
  },
  width: String,
  height: String,
  lines: {
    type: Number,
    default: 1,
  },
});

const skeletonClasses = computed(() => {
  const base = 'animate-pulse bg-cream-300 dark:bg-gray-700';
  
  const variants = {
    text: 'rounded h-4',
    circular: 'rounded-full',
    rectangular: 'rounded-xl',
  };
  
  return [base, variants[props.variant]].join(' ');
});

const customStyle = computed(() => {
  const style = {};
  if (props.width) style.width = props.width;
  if (props.height) style.height = props.height;
  return style;
});
</script>
