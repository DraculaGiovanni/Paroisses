<template>
  <Card padding="none" class="border-t-2" :class="accentBorder">
    <div class="p-4 sm:p-6">
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
          <p class="text-xs font-semibold uppercase tracking-[0.14em] text-gray-500 dark:text-gray-400">
            {{ title }}
          </p>
          <p class="mt-3 font-display text-3xl leading-none text-gray-900 dark:text-white sm:text-4xl">
            {{ value }}
          </p>
        </div>

        <div :class="iconBgColor" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl">
          <svg class="h-5 w-5" :class="iconColor" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path v-if="icon === 'megaphone'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
            <path v-else-if="icon === 'document'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            <path v-else-if="icon === 'clock'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
          </svg>
        </div>
      </div>
    </div>
  </Card>
</template>

<script setup>
import { computed } from 'vue';
import Card from '@/Components/UI/Card.vue';

const props = defineProps({
  title: {
    type: String,
    required: true,
  },
  value: {
    type: [String, Number],
    required: true,
  },
  icon: {
    type: String,
    default: 'chart',
  },
  color: {
    type: String,
    default: 'spiritual',
    validator: (value) => ['spiritual', 'gold', 'green', 'red'].includes(value),
  },
});

const colorMap = {
  spiritual: {
    border: 'border-spiritual-600',
    iconBg: 'bg-spiritual-100 dark:bg-spiritual-900/30',
    icon: 'text-spiritual-600 dark:text-spiritual-300',
  },
  gold: {
    border: 'border-gold-500',
    iconBg: 'bg-gold-100 dark:bg-gold-900/30',
    icon: 'text-gold-700 dark:text-gold-300',
  },
  green: {
    border: 'border-green-600',
    iconBg: 'bg-green-100 dark:bg-green-900/30',
    icon: 'text-green-700 dark:text-green-300',
  },
  red: {
    border: 'border-red-600',
    iconBg: 'bg-red-100 dark:bg-red-900/30',
    icon: 'text-red-700 dark:text-red-300',
  },
};

const accentBorder = computed(() => colorMap[props.color].border);
const iconBgColor = computed(() => colorMap[props.color].iconBg);
const iconColor = computed(() => colorMap[props.color].icon);
</script>
