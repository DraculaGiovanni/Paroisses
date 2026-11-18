<template>
  <Modal :show="show" size="sm" @close="cancel">
    <div class="p-6">
      <!-- Icon -->
      <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full" :class="iconBgClass">
        <svg class="h-6 w-6" :class="iconColorClass" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path v-if="variant === 'danger'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </div>
      
      <!-- Content -->
      <div class="mt-4 text-center">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
          {{ title }}
        </h3>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
          {{ message }}
        </p>
      </div>
      
      <!-- Actions -->
      <div class="mt-6 flex gap-3">
        <Button
          variant="secondary"
          full-width
          @click="cancel"
        >
          {{ cancelText }}
        </Button>
        <Button
          :variant="variant === 'danger' ? 'danger' : 'primary'"
          full-width
          @click="confirm"
        >
          {{ confirmText }}
        </Button>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { computed } from 'vue';
import Modal from './Modal.vue';
import Button from './Button.vue';

const props = defineProps({
  show: Boolean,
  title: {
    type: String,
    default: 'Confirmer l\'action',
  },
  message: {
    type: String,
    default: 'Êtes-vous sûr de vouloir continuer ?',
  },
  confirmText: {
    type: String,
    default: 'Confirmer',
  },
  cancelText: {
    type: String,
    default: 'Annuler',
  },
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'danger'].includes(value),
  },
});

const emit = defineEmits(['confirm', 'cancel']);

const confirm = () => emit('confirm');
const cancel = () => emit('cancel');

const iconBgClass = computed(() => {
  return props.variant === 'danger'
    ? 'bg-red-100 dark:bg-red-900/30'
    : 'bg-spiritual-100 dark:bg-spiritual-900/30';
});

const iconColorClass = computed(() => {
  return props.variant === 'danger'
    ? 'text-red-600 dark:text-red-400'
    : 'text-spiritual-600 dark:text-spiritual-400';
});
</script>
