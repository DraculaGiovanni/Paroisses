<template>
  <component
    :is="href ? Link : 'button'"
    :href="href"
    :type="href ? undefined : type"
    :class="buttonClasses"
    :disabled="disabled || loading"
    @click="handleClick"
  >
    <span v-if="loading" class="mr-2">
      <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
    </span>
    <slot />
  </component>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary', 'ghost', 'danger', 'gold'].includes(value),
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value),
  },
  href: String,
  type: {
    type: String,
    default: 'button',
  },
  disabled: Boolean,
  loading: Boolean,
  fullWidth: Boolean,
});

const emit = defineEmits(['click']);

const buttonClasses = computed(() => {
  const base = 'inline-flex items-center justify-center font-medium rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2';
  
  const variants = {
    primary: 'accent-solid hover:brightness-95 focus:ring-spiritual-500 active:scale-95',
    secondary: 'bg-cream-300 text-spiritual-900 hover:bg-cream-400 focus:ring-cream-400',
    ghost: 'bg-transparent text-spiritual-700 hover:bg-cream-200 focus:ring-spiritual-300',
    danger: 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500 active:scale-95',
    gold: 'bg-gold-500 text-white hover:bg-gold-600 focus:ring-gold-400 active:scale-95',
  };
  
  const sizes = {
    sm: 'min-h-11 px-3 text-sm',
    md: 'min-h-11 px-4 text-base',
    lg: 'min-h-12 px-6 text-lg',
  };
  
  const disabled = props.disabled || props.loading ? 'opacity-50 cursor-not-allowed' : '';
  const width = props.fullWidth ? 'w-full' : '';
  
  return [base, variants[props.variant], sizes[props.size], disabled, width].filter(Boolean).join(' ');
});

const handleClick = (event) => {
  if (!props.disabled && !props.loading) {
    emit('click', event);
  }
};
</script>
