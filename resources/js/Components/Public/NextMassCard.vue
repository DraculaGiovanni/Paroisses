<template>
  <Card v-if="prochaineMesse" padding="none" class="border-gold-200 dark:border-gold-900/60">
    <div class="flex flex-col gap-5 p-6 sm:flex-row sm:items-center sm:p-7">
      <div
        class="flex min-h-20 w-full shrink-0 flex-row items-center justify-center gap-3 rounded-2xl accent-solid px-4 py-3 sm:w-24 sm:flex-col sm:gap-0"
      >
        <span class="text-xs font-semibold uppercase tracking-[0.18em] text-spiritual-100">
          {{ jourCourt }}
        </span>
        <span class="font-display text-3xl leading-none">{{ prochaineMesse.heure }}</span>
      </div>

      <div class="min-w-0 flex-1">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gold-700 dark:text-gold-300">
          {{ countdown }}
        </p>
        <h2 class="mt-1 font-display text-2xl text-gray-900 dark:text-white sm:text-3xl">
          {{ jourLabel }}
        </h2>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
          <span class="font-semibold text-gray-900 dark:text-white">{{ heureLabel }}</span>
          <span v-if="prochaineMesse.lieu"> · {{ prochaineMesse.lieu }}</span>
        </p>
        <p
          v-if="prochaineMesse.status === 'en_cours' && prochaineMesse.until"
          class="mt-2 text-sm font-semibold text-spiritual-700 dark:text-spiritual-300"
        >
          Messe en cours — jusqu’à {{ prochaineMesse.until }}
        </p>
      </div>
    </div>
  </Card>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import Card from '@/Components/UI/Card.vue';

const props = defineProps({
  prochaineMesse: Object,
  serverTime: {
    type: String,
    default: null,
  },
});

const now = ref(props.serverTime ? new Date(props.serverTime) : new Date());
let interval = null;

onMounted(() => {
  interval = setInterval(() => {
    now.value = new Date(now.value.getTime() + 60000);
  }, 60000);
});

onUnmounted(() => {
  if (interval) clearInterval(interval);
});

const JOURS = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
const JOURS_COURTS = ['Dim.', 'Lun.', 'Mar.', 'Mer.', 'Jeu.', 'Ven.', 'Sam.'];

const jourLabel = computed(() => {
  if (!props.prochaineMesse) return '';
  return JOURS[props.prochaineMesse.jour_semaine % 7] || '';
});

const jourCourt = computed(() => {
  if (!props.prochaineMesse) return '';
  return JOURS_COURTS[props.prochaineMesse.jour_semaine % 7] || '';
});

const heureLabel = computed(() => {
  if (!props.prochaineMesse || !props.prochaineMesse.heure) return '';
  return props.prochaineMesse.heure_fin
    ? `${props.prochaineMesse.heure} – ${props.prochaineMesse.heure_fin}`
    : props.prochaineMesse.heure;
});

const countdown = computed(() => {
  if (!props.prochaineMesse) return '';

  const jourActuel = now.value.getDay();
  const jourMesse = props.prochaineMesse.jour_semaine % 7;

  if (jourActuel === jourMesse) {
    return "Aujourd'hui";
  } else if ((jourActuel + 1) % 7 === jourMesse) {
    return 'Demain';
  }

  const joursRestants = (jourMesse - jourActuel + 7) % 7;
  return `Dans ${joursRestants} jour${joursRestants > 1 ? 's' : ''}`;
});
</script>
