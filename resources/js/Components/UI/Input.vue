<template>
  <div class="w-full">
    <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    
    <div class="relative">
      <div v-if="$slots.prefix" class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
        <slot name="prefix" />
      </div>
      
      <input
        :id="id"
        :type="type"
        :value="modelValue"
        :placeholder="placeholder"
        :required="required"
        :disabled="disabled"
        :min="min"
        :class="[
          inputClasses,
          $slots.prefix ? 'pl-10' : '',
          $slots.suffix ? 'pr-10' : ''
        ]"
        @input="$emit('update:modelValue', $event.target.value)"
        @blur="$emit('blur', $event)"
        @focus="$emit('focus', $event)"
      />
      
      <div v-if="$slots.suffix" class="absolute inset-y-0 right-0 pr-3 flex items-center">
        <slot name="suffix" />
      </div>
    </div>
    
    <p v-if="error" class="mt-1.5 text-sm text-red-600 dark:text-red-400">
      {{ error }}
    </p>
    
    <p v-else-if="hint" class="mt-1.5 text-sm text-gray-500 dark:text-gray-400">
      {{ hint }}
    </p>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  modelValue: [String, Number],
  label: String,
  type: {
    type: String,
    default: 'text',
  },
  placeholder: String,
  error: String,
  hint: String,
  required: Boolean,
  disabled: Boolean,
  id: String,
  min: String,
});

defineEmits(['update:modelValue', 'blur', 'focus']);

const inputClasses = computed(() => {
  const base = 'block min-h-11 w-full rounded-xl border-0 py-2.5 px-4 shadow-sm ring-1 ring-inset focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6';
  
  const state = props.error
    ? 'ring-red-300 text-red-900 placeholder:text-red-300 focus:ring-red-500'
    : 'ring-cream-300 dark:ring-gray-700 text-gray-900 dark:text-white placeholder:text-gray-400 focus:ring-spiritual-600';
  
  const disabled = props.disabled ? 'bg-gray-100 dark:bg-gray-800 cursor-not-allowed opacity-60' : 'bg-white dark:bg-gray-900';
  
  return [base, state, disabled].filter(Boolean).join(' ');
});
</script>
