<template>
  <div :class="cardClasses">
    <div v-if="$slots.header" class="px-6 py-4 border-b border-cream-300 dark:border-gray-700">
      <slot name="header" />
    </div>
    
    <div :class="bodyClasses">
      <slot />
    </div>
    
    <div v-if="$slots.footer" class="px-6 py-4 border-t border-cream-300 dark:border-gray-700 bg-cream-100 dark:bg-gray-800">
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  padding: {
    type: String,
    default: 'normal',
    validator: (value) => ['none', 'sm', 'normal', 'lg'].includes(value),
  },
  hover: Boolean,
  shadow: {
    type: String,
    default: 'soft',
    validator: (value) => ['none', 'soft', 'soft-lg'].includes(value),
  },
});

const cardClasses = computed(() => {
  const base = 'bg-white dark:bg-gray-900 border border-cream-300 dark:border-gray-700 rounded-2xl overflow-hidden';
  const shadows = {
    none: '',
    soft: 'shadow-soft',
    'soft-lg': 'shadow-soft-lg',
  };
  const hoverEffect = props.hover ? 'transition-transform duration-200 hover:-translate-y-1 hover:shadow-soft-lg' : '';
  
  return [base, shadows[props.shadow], hoverEffect].filter(Boolean).join(' ');
});

const bodyClasses = computed(() => {
  const paddings = {
    none: '',
    sm: 'p-4',
    normal: 'p-6',
    lg: 'p-8',
  };
  
  return paddings[props.padding];
});
</script>
