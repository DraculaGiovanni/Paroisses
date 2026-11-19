<template>
  <Head :title="`Demande de messe — ${paroisse.nom}`" />

  <main class="min-h-screen bg-cream-50 px-4 py-8 dark:bg-gray-950 sm:px-6 sm:py-12" :style="{ '--accent': paroisse.couleur_principale || '#1E40AF' }">
    <div class="mx-auto grid max-w-6xl gap-8 lg:grid-cols-[16rem_1fr] lg:gap-12">
      <IntentionsNav :paroisse="paroisse" active="nouvelle" />

      <div class="min-w-0">
        <header>
          <p class="text-xs font-semibold uppercase tracking-[0.18em] text-spiritual-700 dark:text-spiritual-300">
            Votre demande
          </p>
          <h1 class="mt-3 font-display text-4xl text-gray-900 dark:text-white sm:text-5xl">
            Confier une intention
          </h1>
          <p class="mt-4 max-w-2xl leading-relaxed text-gray-600 dark:text-gray-400">
            Remplissez ce formulaire pour demander qu'une messe soit célébrée pour votre intention.
            Un code de suivi personnel vous sera remis à l'envoi.
          </p>
        </header>

        <div
          v-if="form.errors.rate_limit"
          role="alert"
          class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-200"
        >
          {{ form.errors.rate_limit }}
        </div>

        <form
          class="mt-8 space-y-6 rounded-3xl border border-cream-300 bg-white p-5 shadow-soft dark:border-gray-700 dark:bg-gray-900 sm:p-8"
          @submit.prevent="submit"
        >
          <div class="grid gap-5 sm:grid-cols-2">
            <Input
              id="nom_demandeur"
              v-model="form.nom_demandeur"
              label="Votre nom"
              placeholder="Nom et prénom"
              :error="form.errors.nom_demandeur"
              required
            />

            <Input
              id="telephone"
              v-model="form.telephone"
              type="tel"
              label="Téléphone"
              placeholder="+229 …"
              hint="Facultatif, pour que la paroisse puisse vous joindre."
              :error="form.errors.telephone"
            />
          </div>

          <Select
            id="type"
            v-model="form.type"
            label="Type de demande"
            placeholder="Choisissez un type"
            :options="typeOptions"
            :error="form.errors.type"
            required
          />

          <Textarea
            id="intention"
            v-model="form.intention"
            label="Votre intention"
            placeholder="Ex. : Pour le repos de l'âme de…"
            hint="Décrivez brièvement votre intention. 300 caractères maximum."
            :maxlength="300"
            :rows="4"
            :error="form.errors.intention"
            required
          />

          <div class="grid gap-5 sm:grid-cols-2">
            <Input
              id="date_souhaitee"
              v-model="form.date_souhaitee"
              type="date"
              label="Date souhaitée"
              hint="Facultatif. Laissez vide si vous n'avez pas de préférence."
              :min="date_min"
              :error="form.errors.date_souhaitee"
            />

            <Select
              id="horaire_id"
              v-model="form.horaire_id"
              label="Messe souhaitée"
              :options="horaireOptions"
              hint="Facultatif. Choisissez l'une des messes de la paroisse."
              :error="form.errors.horaire_id"
            />
          </div>

          <div class="absolute left-[-9999px] top-auto h-px w-px overflow-hidden" aria-hidden="true">
            <label for="reference_colis">Ne pas remplir ce champ</label>
            <input
              id="reference_colis"
              v-model="form.reference_colis"
              type="text"
              name="reference_colis"
              tabindex="-1"
              autocomplete="off"
            />
          </div>

          <input v-model="form.form_token" type="hidden" name="form_token" />

          <p class="rounded-xl bg-cream-100 px-4 py-3 text-sm leading-relaxed text-gray-600 dark:bg-gray-800 dark:text-gray-300">
            Vos informations sont lues uniquement par l'équipe de la paroisse et supprimées
            90 jours après la célébration.
          </p>

          <Button
            type="submit"
            variant="gold"
            size="lg"
            full-width
            :loading="form.processing"
            :disabled="form.processing"
          >
            Envoyer ma demande
          </Button>

          <p class="text-center text-sm text-gray-500 dark:text-gray-400">
            À l'envoi, un code de suivi s'affichera une seule fois. Notez-le ou faites une capture d'écran.
          </p>
        </form>

        <p class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
          Vous avez déjà un code de suivi ?
          <Link
            :href="route('paroisse.intentions.suivi', paroisse.slug)"
            class="font-semibold text-spiritual-700 underline-offset-4 hover:underline dark:text-spiritual-300"
          >
            Suivre ma demande
          </Link>
        </p>
      </div>
    </div>
  </main>
</template>

<script setup>
import { computed, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Button from '@/Components/UI/Button.vue';
import Input from '@/Components/UI/Input.vue';
import Select from '@/Components/UI/Select.vue';
import Textarea from '@/Components/UI/Textarea.vue';
import IntentionsNav from '@/Components/Public/IntentionsNav.vue';

const props = defineProps({
  paroisse: { type: Object, required: true },
  types: { type: Object, required: true },
  horaires: { type: Array, default: () => [] },
  date_min: { type: String, required: true },
  form_token: { type: String, required: true },
});

const form = useForm({
  nom_demandeur: '',
  telephone: '',
  type: '',
  intention: '',
  date_souhaitee: '',
  horaire_id: '',
  reference_colis: '',
  form_token: props.form_token,
});

watch(
  () => props.form_token,
  (token) => {
    form.form_token = token;
  },
);

const typeOptions = computed(() =>
  Object.entries(props.types).map(([value, label]) => ({ value, label })),
);

const horaireOptions = computed(() => [
  { value: '', label: 'Aucune préférence' },
  ...props.horaires.map((horaire) => ({ value: horaire.id, label: horaire.label })),
]);

function submit() {
  form.post(route('paroisse.intentions.store', props.paroisse.slug), {
    preserveScroll: true,
  });
}
</script>
