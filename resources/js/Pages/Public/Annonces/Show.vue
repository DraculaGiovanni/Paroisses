<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
  paroisse: { type: Object, required: true },
  annonce: { type: Object, required: true },
});

const MOIS = [
  'janvier', 'février', 'mars', 'avril', 'mai', 'juin',
  'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre',
];

function formatDate(value) {
  if (!value) return '';
  const [datePart] = value.split(' ');
  const [year, month, day] = datePart.split('-').map(Number);
  if (!year || !month || !day || !MOIS[month - 1]) return value;
  return `${day} ${MOIS[month - 1]} ${year}`;
}
</script>

<template>
  <Head :title="`${annonce.titre} — ${paroisse.nom}`" />

  <main class="min-h-screen bg-cream-50 px-4 py-10 dark:bg-gray-950 sm:px-6 sm:py-14" :style="{ '--accent': paroisse.couleur_principale || '#1E40AF' }">
    <div class="mx-auto grid max-w-6xl gap-10 lg:grid-cols-[13rem_1fr] lg:gap-12">
      <aside class="hidden lg:block">
        <div class="sticky top-8">
          <Link
            :href="route('paroisse.annonces.index', paroisse.slug)"
            class="inline-flex min-h-11 items-center gap-2 text-sm font-semibold text-spiritual-700 dark:text-spiritual-300"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Toutes les annonces
          </Link>
          <p class="mt-8 font-display text-2xl text-gray-900 dark:text-white">
            {{ paroisse.nom }}
          </p>
        </div>
      </aside>

      <article class="min-w-0">
        <Link
          :href="route('paroisse.annonces.index', paroisse.slug)"
          class="inline-flex min-h-11 items-center gap-2 text-sm font-semibold text-spiritual-700 lg:hidden dark:text-spiritual-300"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          Toutes les annonces
        </Link>

        <div class="mt-4 rounded-3xl border border-cream-300 bg-white p-6 shadow-soft dark:border-gray-700 dark:bg-gray-900 sm:p-10 lg:mt-0">
          <div class="flex flex-wrap items-center gap-3">
            <span class="inline-flex rounded-full bg-cream-200 px-3 py-1 text-xs font-semibold text-spiritual-900 dark:bg-gray-700 dark:text-gray-200">
              {{ annonce.categorie_label }}
            </span>
            <span v-if="annonce.epinglee" class="inline-flex items-center gap-1.5 rounded-full bg-gold-100 px-3 py-1 text-xs font-semibold text-gold-800 dark:bg-gold-900/30 dark:text-gold-200">
              <span class="inline-block h-1.5 w-1.5 rounded-full bg-gold-500" aria-hidden="true" />
              À la une
            </span>
          </div>

          <h1 class="mt-6 font-display text-4xl leading-tight text-gray-900 dark:text-white sm:text-5xl">
            {{ annonce.titre }}
          </h1>

          <img
            v-if="annonce.image_url"
            :src="annonce.image_url"
            :alt="annonce.titre"
            class="mt-6 h-64 w-full rounded-2xl object-cover"
          />

          <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
            Publiée le {{ formatDate(annonce.publie_le) }}
          </p>

          <div class="mt-8 border-t border-cream-200 pt-8 dark:border-gray-700">
            <p class="whitespace-pre-line text-base leading-8 text-gray-700 dark:text-gray-300 sm:text-lg">
              {{ annonce.contenu }}
            </p>
          </div>

          <div class="mt-10 flex flex-wrap gap-3 border-t border-cream-200 pt-6 dark:border-gray-700">
            <Link
              :href="route('paroisse.show', paroisse.slug)"
              class="inline-flex min-h-11 items-center rounded-xl bg-cream-100 px-4 text-sm font-semibold text-spiritual-700 hover:bg-cream-200 dark:bg-gray-800 dark:text-spiritual-300"
            >
              Retour à la paroisse
            </Link>
            <Link
              :href="route('paroisse.intentions.nouvelle', paroisse.slug)"
              class="inline-flex min-h-11 items-center rounded-xl bg-spiritual-700 px-4 text-sm font-semibold text-white hover:bg-spiritual-800"
            >
              Demander une messe
            </Link>
          </div>
        </div>
      </article>
    </div>
  </main>
</template>
