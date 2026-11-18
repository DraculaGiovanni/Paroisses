<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition-opacity duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="show"
        class="fixed inset-0 z-50 overflow-y-auto"
        @click="closeOnBackdrop && close()"
      >
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" aria-hidden="true" />
        
        <!-- Modal container -->
        <div class="flex min-h-full items-center justify-center p-4">
          <Transition
            enter-active-class="transition-all duration-300"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition-all duration-200"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
          >
            <div
              v-if="show"
              :class="modalClasses"
              @click.stop
            >
              <!-- Header -->
              <div v-if="$slots.header || title" class="px-6 py-4 border-b border-cream-300 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                  <slot name="header">{{ title }}</slot>
                </h3>
                <button
                  v-if="closeable"
                  type="button"
                  class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 transition-colors"
                  @click="close"
                >
                  <span class="sr-only">Fermer</span>
                  <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
              
              <!-- Body -->
              <div :class="bodyClasses">
                <slot />
              </div>
              
              <!-- Footer -->
              <div v-if="$slots.footer" class="px-6 py-4 border-t border-cream-300 dark:border-gray-700 bg-cream-50 dark:bg-gray-800/50 flex gap-3 justify-end">
                <slot name="footer" />
              </div>
            </div>
          </Transition>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed, watch } from 'vue';

const props = defineProps({
  show: Boolean,
  title: String,
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg', 'xl', 'full'].includes(value),
  },
  closeable: {
    type: Boolean,
    default: true,
  },
  closeOnBackdrop: {
    type: Boolean,
    default: true,
  },
  padding: {
    type: String,
    default: 'normal',
  },
});

const emit = defineEmits(['close', 'update:show']);

const close = () => {
  emit('update:show', false);
  emit('close');
};

const modalClasses = computed(() => {
  const base = 'relative bg-white dark:bg-gray-900 rounded-2xl shadow-soft-lg';
  
  const sizes = {
    sm: 'w-full max-w-md',
    md: 'w-full max-w-lg',
    lg: 'w-full max-w-2xl',
    xl: 'w-full max-w-4xl',
    full: 'w-full max-w-7xl',
  };
  
  return [base, sizes[props.size]].join(' ');
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

// Bloquer le scroll du body quand la modal est ouverte
watch(() => props.show, (newValue) => {
  if (newValue) {
    document.body.style.overflow = 'hidden';
  } else {
    document.body.style.overflow = '';
  }
});
</script>
