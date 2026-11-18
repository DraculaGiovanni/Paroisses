<template>
  <div class="w-full">
    <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    
    <select
      :id="id"
      :value="modelValue"
      :required="required"
      :disabled="disabled"
      :class="selectClasses"
      @change="handleChange"
    >
      <option v-if="placeholder" value="" disabled>{{ placeholder }}</option>
      <option
        v-for="option in options"
        :key="option.value"
        :value="option.value"
      >
        {{ option.label }}
      </option>
    </select>
    
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
  options: {
    type: Array,
    required: true,
  },
  placeholder: String,
  error: String,
  hint: String,
  required: Boolean,
  disabled: Boolean,
  id: String,
});

const emit = defineEmits(['update:modelValue']);

const handleChange = (event) => {
  const value = event.target.value;
  // Si c'est un nombre, on le convertit
  const numValue = Number(value);
  emit('update:modelValue', !isNaN(numValue) && value !== '' ? numValue : value);
};

const selectClasses = computed(() => {
  const base = 'block min-h-11 w-full rounded-xl border-0 py-2.5 pl-4 pr-10 shadow-sm ring-1 ring-inset focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6';
  
  const state = props.error
    ? 'ring-red-300 text-red-900 focus:ring-red-500'
    : 'ring-cream-300 dark:ring-gray-700 text-gray-900 dark:text-white focus:ring-spiritual-600';
  
  const disabled = props.disabled ? 'bg-gray-100 dark:bg-gray-800 cursor-not-allowed opacity-60' : 'bg-white dark:bg-gray-900';
  
  return [base, state, disabled].filter(Boolean).join(' ');
});
</script>
