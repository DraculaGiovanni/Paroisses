<template>
  <section
    class="relative overflow-hidden border-b border-cream-300 bg-cream-100 dark:border-gray-800 dark:bg-gray-900"
    :style="{ '--accent': accentColor }"
  >
    <!-- Motif discret, sans image générique -->
    <div class="pointer-events-none absolute -right-16 -top-10 h-72 w-72 text-cream-400/50 dark:text-gray-700/40" aria-hidden="true">
      <IconChurch class="h-full w-full" />
    </div>

    <div
      class="relative mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:px-6 sm:py-20 lg:grid-cols-[1.1fr_0.9fr] lg:items-center lg:px-8 lg:py-24"
    >
      <div>
        <div v-if="logoUrl" class="mb-5">
          <img :src="logoUrl" :alt="`Logo de ${paroisse.nom}`" class="h-16 w-16 rounded-xl object-contain" />
        </div>

        <p
          class="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-spiritual-700 dark:text-spiritual-300"
          v-motion
          :initial="{ opacity: 0, y: 10 }"
          :enter="{ opacity: 1, y: 0, transition: { duration: 400 } }"
        >
          <span class="inline-block h-2 w-2 rounded-full bg-gold-500" aria-hidden="true" />
          {{ paroisse.diocese ? `Diocèse de ${paroisse.diocese}` : 'Bienvenue à la paroisse' }}
        </p>

        <h1
          class="mt-5 font-display text-4xl font-normal leading-[1.1] tracking-tight text-gray-900 dark:text-white sm:text-5xl lg:text-6xl"
          v-motion
          :initial="{ opacity: 0, y: 14 }"
          :enter="{ opacity: 1, y: 0, transition: { duration: 500, delay: 80 } }"
        >
          {{ paroisse.nom }}
        </h1>

        <p
          class="mt-5 max-w-xl text-base leading-relaxed text-gray-600 dark:text-gray-300 sm:text-lg"
          v-motion
          :initial="{ opacity: 0, y: 14 }"
          :enter="{ opacity: 1, y: 0, transition: { duration: 500, delay: 160 } }"
        >
          Un lieu de prière et de rencontre. Venez célébrer, écouter la Parole et
          confier vos intentions à la communauté.
        </p>

        <div
          v-if="liturgical"
          class="mt-6 inline-flex flex-wrap items-center gap-2 rounded-full border border-cream-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
          v-motion
          :initial="{ opacity: 0 }"
          :enter="{ opacity: 1, transition: { duration: 400, delay: 240 } }"
        >
          <span
            class="inline-block h-3 w-3 rounded-full border border-gray-300 dark:border-gray-600"
            :style="{ backgroundColor: liturgical.color?.hex }"
            aria-hidden="true"
          />
          {{ liturgical.period?.label }} · {{ liturgical.color?.label }}
        </div>

        <div
          class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center"
          v-motion
          :initial="{ opacity: 0, y: 10 }"
          :enter="{ opacity: 1, y: 0, transition: { duration: 400, delay: 300 } }"
        >
          <Button
            :href="route('paroisse.intentions.nouvelle', paroisse.slug)"
            variant="primary"
            size="lg"
          >
            Demander une messe
          </Button>
          <a
            href="#horaires"
            class="inline-flex min-h-12 items-center justify-center rounded-xl px-6 text-lg font-medium text-spiritual-700 transition-colors hover:bg-cream-200 focus:outline-none focus:ring-2 focus:ring-spiritual-500 dark:text-spiritual-300 dark:hover:bg-gray-800"
          >
            Voir les horaires
          </a>
        </div>

        <a
          v-if="whatsappUrl"
          :href="whatsappUrl"
          target="_blank"
          rel="noopener noreferrer"
          class="mt-5 inline-flex min-h-11 items-center gap-2 text-sm font-semibold text-spiritual-700 underline-offset-4 hover:underline dark:text-spiritual-300"
        >
          <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M12.04 2a9.96 9.96 0 00-8.6 14.94L2 22l5.2-1.36A10 10 0 1022 12 10 10 0 0012.04 2zm0 18.2a8.2 8.2 0 01-4.18-1.14l-.3-.18-3.09.81.82-3.01-.2-.31a8.2 8.2 0 112.86 2.76l.31.19 3.78-.99z" />
          </svg>
          Rejoindre le groupe WhatsApp
        </a>

        <div
          v-if="paroisse.adresse || paroisse.telephone"
          class="mt-8 flex flex-wrap gap-x-6 gap-y-2 text-sm text-gray-600 dark:text-gray-400"
        >
          <span v-if="paroisse.adresse" class="inline-flex items-center gap-2">
            <svg class="h-4 w-4 text-gold-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            {{ paroisse.adresse }}
          </span>
          <span v-if="paroisse.telephone" class="inline-flex items-center gap-2">
            <svg class="h-4 w-4 text-gold-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
            </svg>
            {{ paroisse.telephone }}
          </span>
        </div>
      </div>

      <!-- Carte "prochaine messe" : élément le plus visible après le titre -->
      <div
        class="relative mx-auto w-full max-w-md lg:mx-0 lg:justify-self-end"
        v-motion
        :initial="{ opacity: 0, y: 16 }"
        :enter="{ opacity: 1, y: 0, transition: { duration: 500, delay: 200 } }"
      >
        <img
          v-if="banniereUrl"
          :src="banniereUrl"
          :alt="`Bannière de ${paroisse.nom}`"
          class="mb-5 h-52 w-full rounded-3xl object-cover shadow-soft"
        />
        <div class="rounded-3xl border border-cream-300 bg-white p-6 shadow-soft-lg dark:border-gray-700 dark:bg-gray-800 sm:p-8">
          <div class="flex items-center justify-between gap-4">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-spiritual-700 dark:text-spiritual-300">
              Prochaine messe
            </p>
            <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-gold-100 text-gold-700 dark:bg-gold-900/30 dark:text-gold-300">
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </span>
          </div>

          <template v-if="prochaineMesse">
            <p class="mt-6 font-display text-3xl text-gray-900 dark:text-white sm:text-4xl">
              {{ jourLabel }}
            </p>
            <p class="mt-1 text-4xl font-semibold tracking-tight text-gray-900 dark:text-white sm:text-5xl">
              {{ heureLabel }}
            </p>
            <p v-if="prochaineMesse.lieu" class="mt-3 text-sm text-gray-600 dark:text-gray-300">
              {{ prochaineMesse.lieu }}
            </p>
          </template>
          <p v-else class="mt-6 text-sm leading-relaxed text-gray-600 dark:text-gray-300">
            Les horaires de messe seront annoncés prochainement.
          </p>

          <div class="mt-6 border-t border-cream-200 pt-4 dark:border-gray-700">
            <Link
              :href="route('paroisse.annonces.index', paroisse.slug)"
              class="inline-flex min-h-11 items-center gap-2 text-sm font-semibold text-spiritual-700 hover:gap-3 dark:text-spiritual-300"
            >
              Lire les annonces
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7-7 7M21 12H3" />
              </svg>
            </Link>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import Button from '@/Components/UI/Button.vue';
import IconChurch from '@/Components/Icons/IconChurch.vue';

const props = defineProps({
  paroisse: {
    type: Object,
    required: true,
  },
  accentColor: {
    type: String,
    default: '#1E40AF',
  },
  prochaineMesse: {
    type: Object,
    default: null,
  },
  liturgical: {
    type: Object,
    default: null,
  },
  logoUrl: {
    type: String,
    default: null,
  },
  banniereUrl: {
    type: String,
    default: null,
  },
  whatsappUrl: {
    type: String,
    default: null,
  },
});

const JOURS = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];

const jourLabel = computed(() => {
  if (!props.prochaineMesse) return '';
  return JOURS[props.prochaineMesse.jour_semaine % 7] || '';
});

const heureLabel = computed(() => {
  if (!props.prochaineMesse?.heure) return '';
  return props.prochaineMesse.heure_fin
    ? `${props.prochaineMesse.heure} – ${props.prochaineMesse.heure_fin}`
    : props.prochaineMesse.heure;
});
</script>
