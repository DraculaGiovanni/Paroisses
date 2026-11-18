<template>
  <Head :title="paroisse.nom" />
  
  <div class="min-h-screen" :style="cssVars">
    <!-- Hero Section -->
    <HeroSection :paroisse="paroisse" :accent-color="couleur_principale" />
    
    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div
        v-if="liturgical"
        class="mb-6 rounded-xl border border-cream-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-3 text-sm"
      >
        <span
          class="inline-block h-3 w-3 rounded-full mr-2 border border-gray-300"
          :style="{ backgroundColor: liturgical.color.hex }"
          aria-hidden="true"
        />
        <span class="font-semibold">{{ liturgical.period.label }}</span>
        <span class="text-gray-600 dark:text-gray-400"> — {{ liturgical.color.label }}</span>
      </div>

      <!-- Prochaine messe -->
      <div v-if="prochaine_messe" class="mb-12">
        <NextMassCard :prochaine-messe="prochaine_messe" :server-time="server_time" />
      </div>
      
      <!-- Horaires -->
      <WeekSchedule :horaires="horaires" />
      
      <!-- Annonces -->
      <section>
        <div class="mb-6 flex items-center justify-between gap-3">
          <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
            Annonces
          </h2>
          <Link :href="route('paroisse.annonces.index', paroisse.slug)" class="text-sm font-semibold text-spiritual-700">
            Historique
          </Link>
        </div>
        
        <div v-if="annonces.length > 0" class="grid gap-6 md:grid-cols-2">
          <div
            v-for="(annonce, index) in annonces"
            :key="annonce.id"
            v-motion
            :initial="{ opacity: 0, y: 20 }"
            :enter="{ opacity: 1, y: 0, transition: { duration: 400, delay: index * 60 } }"
          >
            <AnnonceCard :annonce="annonce" />
          </div>
        </div>
        
        <EmptyState
          v-else
          title="Aucune annonce"
          description="Il n'y a pas d'annonce pour le moment."
        >
          <template #icon>
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
            </svg>
          </template>
        </EmptyState>
      </section>

      <!-- Demandes de messe -->
      <section class="mt-12 rounded-2xl border border-cream-300 bg-white p-6 shadow-soft dark:border-gray-700 dark:bg-gray-900 sm:p-8">
        <div class="sm:flex sm:items-center sm:justify-between sm:gap-8">
          <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
              Demander une messe
            </h2>
            <p class="mt-2 max-w-xl text-gray-600 dark:text-gray-400">
              Confiez une intention à la paroisse. Vous recevrez un code personnel
              pour suivre l'état de votre demande, sans afficher vos informations.
            </p>
          </div>

          <div class="mt-5 flex flex-col gap-3 sm:mt-0 sm:flex-shrink-0">
            <Button
              :href="route('paroisse.intentions.nouvelle', paroisse.slug)"
              variant="primary"
              class="min-h-11"
            >
              Demander une messe
            </Button>
            <Button
              :href="route('paroisse.intentions.suivi', paroisse.slug)"
              variant="ghost"
              class="min-h-11"
            >
              Suivre ma demande
            </Button>
          </div>
        </div>
      </section>

      <!-- Footer -->
      <footer class="mt-16 pt-8 border-t border-cream-300 dark:border-gray-700 text-center text-sm text-gray-600 dark:text-gray-400">
        <p>&copy; {{ new Date().getFullYear() }} {{ paroisse.nom }}. Tous droits réservés.</p>
      </footer>
    </main>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import HeroSection from '@/Components/Public/HeroSection.vue';
import NextMassCard from '@/Components/Public/NextMassCard.vue';
import WeekSchedule from '@/Components/Public/WeekSchedule.vue';
import AnnonceCard from '@/Components/Public/AnnonceCard.vue';
import Button from '@/Components/UI/Button.vue';
import EmptyState from '@/Components/UI/EmptyState.vue';

const props = defineProps({
  paroisse: {
    type: Object,
    required: true,
  },
  couleur_principale: {
    type: String,
    default: '#1E40AF',
  },
  horaires: {
    type: Array,
    default: () => [],
  },
  annonces: {
    type: Array,
    default: () => [],
  },
  prochaine_messe: Object,
  server_time: {
    type: String,
    default: null,
  },
  liturgical: {
    type: Object,
    default: null,
  },
});

// Vérifier le contraste de la couleur personnalisée
const isColorValid = (color) => {
  // Simple validation hex
  return /^#[0-9A-F]{6}$/i.test(color);
};

const accentColor = computed(() => {
  return isColorValid(props.couleur_principale) ? props.couleur_principale : '#1E40AF';
});

const cssVars = computed(() => ({
  '--accent': accentColor.value,
}));
</script>
