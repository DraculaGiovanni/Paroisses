<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import EmptyState from '@/Components/UI/EmptyState.vue';

const props = defineProps({
  paroisse: { type: Object, required: true },
  annonces: { type: Object, required: true },
  categorie: { type: String, default: null },
  categories: { type: Array, default: () => [] },
});

const MOIS_COURTS = ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];

function dateParts(value) {
  if (!value) return { jour: '', mois: '' };
  const [datePart] = value.split(' ');
  const [year, month, day] = datePart.split('-').map(Number);
  if (!year || !month || !day) return { jour: '', mois: '' };
  return { jour: String(day).padStart(2, '0'), mois: MOIS_COURTS[month - 1] || '' };
}

function formatDate(value) {
  const { jour, mois } = dateParts(value);
  return mois ? `${jour} ${mois}` : '';
}

const activeLabel = computed(
  () => props.categories.find((item) => item.value === props.categorie)?.label ?? null,
);

const aLaUne = computed(() => props.annonces.data.filter((annonce) => annonce.epinglee));

function categoryHref(value) {
  const base = route('paroisse.annonces.index', props.paroisse.slug);
  return value ? `${base}?categorie=${encodeURIComponent(value)}` : base;
}
</script>

<template>
  <Head :title="`Annonces — ${paroisse.nom}`" />

  <main class="min-h-screen bg-cream-50 px-4 py-10 dark:bg-gray-950 sm:px-6 sm:py-14" :style="{ '--accent': paroisse.couleur_principale || '#1E40AF' }">
    <div class="mx-auto grid max-w-7xl gap-10 lg:grid-cols-[13rem_1fr] xl:grid-cols-[13rem_1fr_13rem] xl:gap-12">
      <!-- Navigation latérale -->
      <aside class="hidden lg:block">
        <div class="sticky top-8">
          <Link
            :href="route('paroisse.show', paroisse.slug)"
            class="inline-flex min-h-11 items-center gap-2 text-sm font-semibold text-spiritual-700 dark:text-spiritual-300"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            {{ paroisse.nom }}
          </Link>

          <nav class="mt-8 space-y-1 border-l border-cream-300 dark:border-gray-700" aria-label="Liens rapides">
            <Link
              :href="route('paroisse.annonces.index', paroisse.slug)"
              class="flex min-h-11 items-center border-l-2 border-gold-500 pl-4 text-sm font-semibold text-spiritual-800 dark:text-spiritual-200"
            >
              Toutes les annonces
            </Link>
            <Link
              :href="route('paroisse.intentions.nouvelle', paroisse.slug)"
              class="flex min-h-11 items-center border-l-2 border-transparent pl-4 text-sm text-gray-600 hover:text-spiritual-700 dark:text-gray-400 dark:hover:text-spiritual-300"
            >
              Demander une messe
            </Link>
            <Link
              :href="route('paroisse.intentions.suivi', paroisse.slug)"
              class="flex min-h-11 items-center border-l-2 border-transparent pl-4 text-sm text-gray-600 hover:text-spiritual-700 dark:text-gray-400 dark:hover:text-spiritual-300"
            >
              Suivre ma demande
            </Link>
          </nav>
        </div>
      </aside>

      <!-- Fil d'annonces -->
      <div class="min-w-0">
        <Link
          :href="route('paroisse.show', paroisse.slug)"
          class="inline-flex min-h-11 items-center gap-2 text-sm font-semibold text-spiritual-700 lg:hidden dark:text-spiritual-300"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          {{ paroisse.nom }}
        </Link>

        <header class="mt-4 lg:mt-0">
          <p class="text-xs font-semibold uppercase tracking-[0.18em] text-spiritual-700 dark:text-spiritual-300">
            La vie de la paroisse
          </p>
          <h1 class="mt-3 font-display text-4xl text-gray-900 dark:text-white sm:text-5xl">
            Annonces
          </h1>
          <p v-if="activeLabel" class="mt-3 text-sm text-gray-600 dark:text-gray-400">
            Filtre : <span class="font-semibold text-gray-900 dark:text-white">{{ activeLabel }}</span>
          </p>
        </header>

        <div class="mt-8 flex flex-wrap gap-2" aria-label="Filtrer les annonces">
          <Link
            :href="categoryHref(null)"
            :class="[
              'inline-flex min-h-11 items-center rounded-full border px-4 text-sm font-semibold transition-colors',
              !categorie
                ? 'border-spiritual-700 bg-spiritual-700 text-white'
                : 'border-cream-300 bg-white text-gray-700 hover:border-spiritual-400 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200',
            ]"
          >
            Toutes
          </Link>
          <Link
            v-for="item in categories"
            :key="item.value"
            :href="categoryHref(item.value)"
            :class="[
              'inline-flex min-h-11 items-center rounded-full border px-4 text-sm font-semibold transition-colors',
              categorie === item.value
                ? 'border-spiritual-700 bg-spiritual-700 text-white'
                : 'border-cream-300 bg-white text-gray-700 hover:border-spiritual-400 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200',
            ]"
          >
            {{ item.label }}
          </Link>
        </div>

        <div v-if="annonces.data.length" class="mt-8 space-y-4">
          <Link
            v-for="annonce in annonces.data"
            :key="annonce.id"
            :href="route('paroisse.annonces.show', [paroisse.slug, annonce.id])"
            class="group flex flex-col gap-4 rounded-3xl border border-cream-300 bg-white p-5 shadow-soft transition-transform duration-200 hover:-translate-y-0.5 focus:outline-none focus-visible:ring-2 focus-visible:ring-spiritual-600 dark:border-gray-700 dark:bg-gray-900 sm:flex-row sm:items-center sm:p-6"
            :class="annonce.epinglee ? 'border-gold-300 dark:border-gold-800' : ''"
          >
            <div
              class="flex w-full shrink-0 items-center gap-3 rounded-2xl bg-cream-100 px-4 py-3 text-spiritual-800 dark:bg-gray-800 dark:text-spiritual-200 sm:w-20 sm:flex-col sm:gap-0 sm:py-4"
            >
              <span class="font-display text-2xl leading-none">{{ dateParts(annonce.publie_le).jour }}</span>
              <span class="text-xs font-semibold uppercase tracking-wide">{{ dateParts(annonce.publie_le).mois }}</span>
            </div>

            <div class="min-w-0 flex-1">
              <img
                v-if="annonce.image_url"
                :src="annonce.image_url"
                :alt="annonce.titre"
                class="mb-4 h-36 w-full rounded-2xl object-cover"
                loading="lazy"
              />
              <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex rounded-full bg-cream-200 px-2.5 py-1 text-xs font-semibold text-spiritual-900 dark:bg-gray-700 dark:text-gray-200">
                  {{ annonce.epinglee ? 'À la une' : annonce.categorie_label }}
                </span>
                <span v-if="annonce.epinglee" class="text-xs text-gray-500 dark:text-gray-400">
                  {{ annonce.categorie_label }}
                </span>
              </div>
              <h2 class="mt-3 font-display text-xl text-gray-900 dark:text-white sm:text-2xl">
                {{ annonce.titre }}
              </h2>
              <p class="mt-2 line-clamp-2 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                {{ annonce.contenu }}
              </p>
            </div>

            <span class="inline-flex shrink-0 items-center gap-2 self-end text-sm font-semibold text-spiritual-700 transition-all group-hover:gap-3 dark:text-spiritual-300 sm:self-center">
              Lire
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7-7 7M21 12H3" />
              </svg>
            </span>
          </Link>
        </div>

        <EmptyState v-else title="Aucune annonce" description="Aucune annonce n'est publiée pour le moment." />

        <nav
          v-if="annonces.last_page > 1"
          class="mt-8 flex flex-wrap items-center justify-between gap-4 text-sm"
          aria-label="Pagination"
        >
          <Link
            v-if="annonces.prev_page_url"
            :href="annonces.prev_page_url"
            class="inline-flex min-h-11 items-center gap-2 rounded-xl border border-cream-300 bg-white px-4 font-semibold text-spiritual-700 dark:border-gray-700 dark:bg-gray-900 dark:text-spiritual-300"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m7 7l-7-7 7-7" />
            </svg>
            Précédent
          </Link>
          <span v-else class="text-gray-400">Précédent</span>

          <span class="text-gray-600 dark:text-gray-400">
            Page {{ annonces.current_page }} sur {{ annonces.last_page }}
          </span>

          <Link
            v-if="annonces.next_page_url"
            :href="annonces.next_page_url"
            class="inline-flex min-h-11 items-center gap-2 rounded-xl border border-cream-300 bg-white px-4 font-semibold text-spiritual-700 dark:border-gray-700 dark:bg-gray-900 dark:text-spiritual-300"
          >
            Suivant
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7-7l7 7-7 7" />
            </svg>
          </Link>
          <span v-else class="text-gray-400">Suivant</span>
        </nav>
      </div>

      <!-- Colonne complémentaire -->
      <aside class="hidden xl:block">
        <div class="sticky top-8 space-y-8">
          <div class="rounded-3xl border border-cream-300 bg-white p-5 dark:border-gray-700 dark:bg-gray-900">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gold-700 dark:text-gold-300">
              À la une
            </p>
            <ul v-if="aLaUne.length" class="mt-4 space-y-4">
              <li v-for="item in aLaUne.slice(0, 3)" :key="item.id">
                <Link
                  :href="route('paroisse.annonces.show', [paroisse.slug, item.id])"
                  class="font-display text-base leading-snug text-gray-900 hover:text-spiritual-700 dark:text-white dark:hover:text-spiritual-300"
                >
                  {{ item.titre }}
                </Link>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ formatDate(item.publie_le) }}</p>
              </li>
            </ul>
            <p v-else class="mt-4 text-sm text-gray-500 dark:text-gray-400">
              Aucune annonce épinglée.
            </p>
          </div>

          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-spiritual-700 dark:text-spiritual-300">
              Liens utiles
            </p>
            <div class="mt-4 flex flex-wrap gap-2">
              <Link
                v-for="item in categories.slice(0, 6)"
                :key="item.value"
                :href="categoryHref(item.value)"
                class="inline-flex min-h-11 items-center rounded-full border border-cream-300 bg-white px-3 text-xs font-semibold text-gray-700 hover:border-spiritual-400 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
              >
                {{ item.label }}
              </Link>
            </div>
          </div>
        </div>
      </aside>
    </div>
  </main>
</template>
