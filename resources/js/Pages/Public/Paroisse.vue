<template>
  <Head :title="paroisse.nom" />

  <div class="min-h-screen bg-cream-50 dark:bg-gray-950" :style="cssVars">
    <HeroSection
      :paroisse="paroisse"
      :accent-color="accentColor"
      :prochaine-messe="prochaine_messe"
      :liturgical="liturgical"
      :logo-url="paroisse.logo_url"
      :banniere-url="paroisse.banniere_url"
      :whatsapp-url="paroisse.whatsapp_channel_url"
    />

    <main class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
      <!-- Bandeau liturgique : la couleur du jour reste l'accent, toujours accompagnée d'un texte -->
      <div
        v-if="liturgical"
        class="mb-10 inline-flex flex-wrap items-center gap-2 rounded-full border border-cream-300 bg-white px-4 py-2 text-sm dark:border-gray-700 dark:bg-gray-900"
      >
        <span
          class="inline-block h-3 w-3 rounded-full border border-gray-300 dark:border-gray-600"
          :style="{ backgroundColor: liturgical.color?.hex }"
          aria-hidden="true"
        />
        <span class="font-semibold text-gray-900 dark:text-white">{{ liturgical.period?.label }}</span>
        <span class="text-gray-600 dark:text-gray-300">— {{ liturgical.color?.label }}</span>
      </div>

      <div class="grid gap-10 lg:grid-cols-2 lg:gap-12">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.18em] text-spiritual-700 dark:text-spiritual-300">
            Célébrer ensemble
          </p>
          <h2 class="mt-2 font-display text-3xl text-gray-900 dark:text-white sm:text-4xl">
            Le temps de la messe
          </h2>
          <div class="mt-6">
            <NextMassCard :prochaine-messe="prochaine_messe" :server-time="server_time" />
          </div>
        </div>

        <div class="min-w-0">
          <p class="text-xs font-semibold uppercase tracking-[0.18em] text-spiritual-700 dark:text-spiritual-300">
            Toute la semaine
          </p>
          <div class="mt-6">
            <WeekSchedule :horaires="horaires" />
          </div>
        </div>
      </div>

      <!-- Annonces -->
      <section class="mt-16 sm:mt-20">
        <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-spiritual-700 dark:text-spiritual-300">
              La vie de la paroisse
            </p>
            <h2 class="mt-2 font-display text-3xl text-gray-900 dark:text-white sm:text-4xl">
              Annonces
            </h2>
          </div>
          <Link
            :href="route('paroisse.annonces.index', paroisse.slug)"
            class="inline-flex min-h-11 items-center gap-2 text-sm font-semibold text-spiritual-700 hover:gap-3 dark:text-spiritual-300"
          >
            Toutes les annonces
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7-7 7M21 12H3" />
            </svg>
          </Link>
        </div>

        <div v-if="annonces.length > 0" class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
          <AnnonceCard
            v-for="(annonce, index) in annonces.slice(0, 6)"
            :key="annonce.id"
            :annonce="annonce"
            v-motion
            :initial="{ opacity: 0, y: 14 }"
            :enter="{ opacity: 1, y: 0, transition: { duration: 400, delay: Math.min(index, 5) * 60 } }"
          />
        </div>
        <EmptyState v-else title="Aucune annonce" description="Il n'y a pas d'annonce pour le moment." />
      </section>

      <!-- Appel à l'intention de messe -->
      <section class="mt-16 rounded-3xl border border-cream-300 bg-white p-6 shadow-soft dark:border-gray-700 dark:bg-gray-900 sm:p-10">
        <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between sm:gap-10">
          <div class="max-w-2xl">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gold-700 dark:text-gold-300">
              Demande de messe
            </p>
            <h2 class="mt-2 font-display text-3xl text-gray-900 dark:text-white sm:text-4xl">
              Confiez-nous votre intention
            </h2>
            <p class="mt-3 leading-relaxed text-gray-600 dark:text-gray-300">
              Vous recevrez un code personnel pour suivre votre demande, sans afficher vos informations.
            </p>
          </div>

          <div class="flex flex-col gap-3 sm:w-auto sm:shrink-0">
            <Button
              :href="route('paroisse.intentions.nouvelle', paroisse.slug)"
              variant="gold"
              size="lg"
            >
              Demander une messe
            </Button>
            <Button
              :href="route('paroisse.intentions.suivi', paroisse.slug)"
              variant="ghost"
              size="lg"
            >
              Suivre ma demande
            </Button>
          </div>
        </div>
      </section>

      <!-- Avis anonymes -->
      <section id="avis" class="mt-16 scroll-mt-8 sm:mt-20">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-spiritual-700 dark:text-spiritual-300">
          La parole des paroissiens
        </p>
        <h2 class="mt-2 font-display text-3xl text-gray-900 dark:text-white sm:text-4xl">
          Donnez votre avis
        </h2>

        <div class="mt-6 grid gap-6 lg:grid-cols-2 lg:gap-10">
          <div class="rounded-3xl border border-cream-300 bg-white p-5 shadow-soft dark:border-gray-700 dark:bg-gray-900 sm:p-7">
            <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-400">
              Un mot, une suggestion, une inquiétude ? Votre message est transmis
              à l'équipe de la paroisse sans nom ni coordonnée.
            </p>

            <div v-if="form.errors.rate_limit" role="alert" class="mt-4 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-800 dark:bg-red-950/30 dark:text-red-200">
              {{ form.errors.rate_limit }}
            </div>

            <form class="mt-5 space-y-4" @submit.prevent="submitAvis">
              <div>
                <label for="avis_message" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                  Votre avis <span class="text-red-500">*</span>
                </label>
                <textarea
                  id="avis_message"
                  v-model="form.message"
                  rows="4"
                  maxlength="1000"
                  required
                  placeholder="Écrivez votre message…"
                  class="block min-h-11 w-full rounded-xl border-0 px-4 py-3 shadow-sm ring-1 ring-inset ring-cream-300 focus:ring-2 focus:ring-spiritual-600 dark:bg-gray-800 dark:text-white dark:ring-gray-700"
                />
                <p v-if="form.errors.message" class="mt-1.5 text-sm text-red-600 dark:text-red-400">{{ form.errors.message }}</p>
                <p v-else class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">1 000 caractères maximum.</p>
              </div>

              <div class="absolute left-[-9999px] top-auto h-px w-px overflow-hidden" aria-hidden="true">
                <label for="reference_avis">Ne pas remplir ce champ</label>
                <input id="reference_avis" v-model="form.reference_colis" type="text" name="reference_colis" tabindex="-1" autocomplete="off" />
              </div>
              <input v-model="form.form_token" type="hidden" name="form_token" />

              <Button type="submit" variant="gold" :loading="form.processing" :disabled="form.processing">
                Envoyer mon avis
              </Button>
              <p v-if="flashSuccess" role="status" class="text-sm font-semibold text-green-700 dark:text-green-300">
                Merci, votre avis a bien été transmis.
              </p>
            </form>
          </div>

          <div class="rounded-3xl bg-cream-100 p-5 dark:bg-gray-900 sm:p-7">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gold-700 dark:text-gold-300">
              Avis partagés
            </p>
            <ul v-if="avis.length" class="mt-5 space-y-5">
              <li v-for="(item, index) in avis" :key="index" class="border-l-2 border-gold-400 pl-4">
                <p class="whitespace-pre-line leading-relaxed text-gray-700 dark:text-gray-300">
                  « {{ item.message }} »
                </p>
              </li>
            </ul>
            <p v-else class="mt-5 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
              Aucun avis publié pour le moment. Les messages sont modérés par la paroisse avant d'apparaître ici.
            </p>
          </div>
        </div>
      </section>

      <footer class="mt-16 flex flex-wrap items-center justify-between gap-3 border-t border-cream-300 pt-8 text-sm text-gray-600 dark:border-gray-700 dark:text-gray-400">
        <p>&copy; {{ new Date().getFullYear() }} {{ paroisse.nom }}. Tous droits réservés.</p>
        <p v-if="paroisse.diocese">Diocèse de {{ paroisse.diocese }}</p>
      </footer>
    </main>
  </div>
</template>

<script setup>
import { computed, watch } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
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
  avis: {
    type: Array,
    default: () => [],
  },
  avis_form_token: {
    type: String,
    default: '',
  },
});

const form = useForm({
  message: '',
  reference_colis: '',
  form_token: props.avis_form_token,
});

watch(
  () => props.avis_form_token,
  (token) => {
    form.form_token = token;
  },
);

const flashSuccess = computed(() => usePage().props.flash?.success || '');

function submitAvis() {
  form.post(route('paroisse.avis.store', props.paroisse.slug), {
    preserveScroll: true,
    onSuccess: () => form.reset('message'),
  });
}

const isColorValid = (color) => /^#[0-9A-F]{6}$/i.test(color);

const accentColor = computed(() => {
  return isColorValid(props.couleur_principale) ? props.couleur_principale : '#1E40AF';
});

const cssVars = computed(() => ({
  '--accent': accentColor.value,
}));
</script>
