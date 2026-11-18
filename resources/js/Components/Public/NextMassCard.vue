<template>
  <Card 
    v-if="prochaineMesse"
    class="bg-gradient-to-br from-gold-50 to-gold-100 dark:from-gold-900/20 dark:to-gold-800/20 border-gold-200 dark:border-gold-700"
    v-motion
    :initial="{ opacity: 0, scale: 0.95 }"
    :enter="{ opacity: 1, scale: 1, transition: { duration: 400, delay: 200 } }"
  >
    <div class="p-6">
      <div class="flex items-center gap-3 mb-4">
        <div class="p-3 bg-gold-500 text-white rounded-xl">
          <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
          Prochaine messe
        </h2>
      </div>
      
      <div class="space-y-2">
        <p class="text-lg font-semibold text-gold-900 dark:text-gold-100">
          {{ jourLabel }}
        </p>
        <p class="text-3xl font-bold text-gray-900 dark:text-white">
          {{ heureLabel }}
        </p>
        <p v-if="prochaineMesse.status === 'en_cours' && prochaineMesse.until" class="text-sm font-semibold text-gold-700 dark:text-gold-300">
          Messe en cours — jusqu’à {{ prochaineMesse.until }}
        </p>
        <p v-if="prochaineMesse.lieu" class="text-gray-700 dark:text-gray-300">
          {{ prochaineMesse.lieu }}
        </p>
        <p v-if="countdown" class="text-sm text-gold-700 dark:text-gold-300 mt-4">
          {{ countdown }}
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

// Initialiser avec le temps serveur si disponible, sinon utiliser le temps local
const now = ref(props.serverTime ? new Date(props.serverTime) : new Date());
let interval = null;

onMounted(() => {
  // Mettre à jour chaque minute (pas chaque seconde pour économiser les ressources)
  interval = setInterval(() => {
    now.value = new Date(now.value.getTime() + 60000);
  }, 60000);
});

onUnmounted(() => {
  if (interval) clearInterval(interval);
});

const JOURS = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];

const jourLabel = computed(() => {
  if (!props.prochaineMesse) return '';
  return JOURS[props.prochaineMesse.jour_semaine % 7] || '';
});

const heureLabel = computed(() => {
  if (!props.prochaineMesse || !props.prochaineMesse.heure) return '';
  return props.prochaineMesse.heure_fin
    ? `${props.prochaineMesse.heure} – ${props.prochaineMesse.heure_fin}`
    : props.prochaineMesse.heure;
});

const countdown = computed(() => {
  if (!props.prochaineMesse) return '';
  
  // Calculer le temps restant
  const jourActuel = now.value.getDay();
  const jourMesse = props.prochaineMesse.jour_semaine % 7;
  
  if (jourActuel === jourMesse) {
    return "Aujourd'hui";
  } else if ((jourActuel + 1) % 7 === jourMesse) {
    return "Demain";
  } else {
    const joursRestants = (jourMesse - jourActuel + 7) % 7;
    return `Dans ${joursRestants} jour${joursRestants > 1 ? 's' : ''}`;
  }
});
</script>
