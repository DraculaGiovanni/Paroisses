<template>
  <section id="horaires" class="scroll-mt-8">
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
      <div>
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-spiritual-700 dark:text-spiritual-300">
          Se retrouver
        </p>
        <h2 class="mt-2 font-display text-3xl text-gray-900 dark:text-white sm:text-4xl">
          Horaires des messes
        </h2>
      </div>
    </div>

    <div
      v-if="horairesByDay.length > 0"
      class="divide-y divide-cream-200 overflow-hidden rounded-3xl border border-cream-300 bg-white shadow-soft dark:divide-gray-700 dark:border-gray-700 dark:bg-gray-900"
    >
      <div
        v-for="jour in horairesByDay"
        :key="jour.num"
        class="grid gap-4 p-5 sm:grid-cols-[9rem_1fr] sm:gap-6 sm:p-6"
        v-motion
        :initial="{ opacity: 0, y: 12 }"
        :enter="{ opacity: 1, y: 0, transition: { duration: 400 } }"
      >
        <div>
          <p class="font-display text-xl text-gray-900 dark:text-white sm:text-2xl">
            {{ jour.nom }}
          </p>
          <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            {{ jour.horaires.length }} {{ jour.horaires.length > 1 ? 'messes' : 'messe' }}
          </p>
        </div>

        <div class="space-y-3">
          <div
            v-for="horaire in jour.horaires"
            :key="horaire.id"
            class="flex flex-col gap-2 rounded-2xl bg-cream-50 px-4 py-3 dark:bg-gray-800 sm:flex-row sm:items-center sm:gap-5"
          >
            <span
              class="inline-flex min-h-11 w-fit items-center rounded-lg accent-solid px-3 text-sm font-semibold"
            >
              {{ horaire.heure_fin ? `${horaire.heure} – ${horaire.heure_fin}` : horaire.heure }}
            </span>
            <div class="min-w-0">
              <p class="font-medium text-gray-900 dark:text-white">{{ horaire.type }}</p>
              <p v-if="horaire.lieu || horaire.note" class="mt-0.5 text-sm text-gray-600 dark:text-gray-400">
                {{ [horaire.lieu, horaire.note].filter(Boolean).join(' · ') }}
              </p>
            </div>
          </div>
        </div>
      </div>
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
  return JOURS.map((jour) => ({
    ...jour,
    horaires: props.horaires.filter((h) => h.jour_semaine === jour.num),
  })).filter((jour) => jour.horaires.length > 0);
});
</script>
