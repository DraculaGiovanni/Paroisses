<template>
  <section class="mb-12">
    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
      Horaires de la semaine
    </h2>
    
    <div v-if="horaires.length > 0" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
      <Card
        v-for="(jour, index) in horairesByDay"
        :key="jour.num"
        hover
        v-motion
        :initial="{ opacity: 0, y: 20 }"
        :enter="{ opacity: 1, y: 0, transition: { duration: 400, delay: index * 60 } }"
      >
        <div class="p-5">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
            {{ jour.nom }}
          </h3>
          
          <div class="space-y-3">
            <div
              v-for="horaire in jour.horaires"
              :key="horaire.id"
              class="flex items-start gap-3"
            >
              <div class="flex-shrink-0 w-20 text-spiritual-700 dark:text-spiritual-400 font-medium">
                {{ horaire.heure_fin ? `${horaire.heure} – ${horaire.heure_fin}` : horaire.heure }}
              </div>
              <div class="flex-1">
                <div class="font-medium text-gray-900 dark:text-white">
                  {{ horaire.type }}
                </div>
                <div v-if="horaire.lieu" class="text-sm text-gray-600 dark:text-gray-400">
                  {{ horaire.lieu }}
                </div>
                <div v-if="horaire.note" class="text-sm text-gray-500 dark:text-gray-500 mt-1">
                  {{ horaire.note }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </Card>
    </div>
    
    <EmptyState
      v-else
      title="Aucun horaire"
      description="Les horaires de messe seront bientôt disponibles."
    >
      <template #icon>
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </template>
    </EmptyState>
  </section>
</template>

<script setup>
import { computed } from 'vue';
import Card from '@/Components/UI/Card.vue';
import EmptyState from '@/Components/UI/EmptyState.vue';

const props = defineProps({
  horaires: {
    type: Array,
    default: () => [],
  },
});

const JOURS = [
  { num: 1, nom: 'Lundi' },
  { num: 2, nom: 'Mardi' },
  { num: 3, nom: 'Mercredi' },
  { num: 4, nom: 'Jeudi' },
  { num: 5, nom: 'Vendredi' },
  { num: 6, nom: 'Samedi' },
  { num: 7, nom: 'Dimanche' },
];

const horairesByDay = computed(() => {
  return JOURS.map(jour => ({
    ...jour,
    horaires: props.horaires.filter(h => h.jour_semaine === jour.num),
  })).filter(jour => jour.horaires.length > 0);
});
</script>
