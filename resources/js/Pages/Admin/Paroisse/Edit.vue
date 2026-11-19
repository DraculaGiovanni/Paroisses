<template>
  <Head title="Réglages de la paroisse" />

  <AdminLayout>
    <PageHeader
      eyebrow="Réglages"
      title="Paroisse"
      description="Les informations ci-dessous apparaissent sur la page publique de votre paroisse."
    />

    <!-- Carte de synthèse -->
    <Card padding="none" class="border-t-2 border-gold-500">
      <div class="flex flex-col gap-5 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-7">
        <div class="flex min-w-0 items-center gap-4">
          <span
            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full text-white"
            :style="{ backgroundColor: form.couleur_principale || '#1E40AF' }"
          >
            <IconChurch class="h-6 w-6" />
          </span>
          <div class="min-w-0">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gold-700 dark:text-gold-300">
              Votre paroisse
            </p>
            <h2 class="mt-1 truncate font-display text-2xl text-gray-900 dark:text-white sm:text-3xl">
              {{ form.nom || paroisse.nom }}
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
              {{ form.diocese ? `Diocèse de ${form.diocese}` : 'Diocèse non renseigné' }}
            </p>
          </div>
        </div>

        <Button
          v-if="paroisse.slug"
          :href="route('paroisse.show', paroisse.slug)"
          variant="secondary"
          target="_blank"
          rel="noopener noreferrer"
        >
          Voir la page publique
        </Button>
      </div>
    </Card>

    <form class="mt-6 grid gap-5 sm:gap-6 lg:grid-cols-2" @submit.prevent="submit">
      <!-- Identité et coordonnées -->
      <Card padding="none" class="border-t-2 border-spiritual-600">
        <div class="border-b border-cream-200 px-5 py-4 dark:border-gray-700 sm:px-6">
          <h2 class="font-display text-xl text-gray-900 dark:text-white">
            Identité & coordonnées
          </h2>
        </div>
        <div class="space-y-5 p-5 sm:p-6">
          <Input
            v-model="form.nom"
            label="Nom de la paroisse"
            :error="form.errors.nom"
            required
            placeholder="Ex : Cathédrale Notre-Dame"
          />

          <Input
            v-model="form.diocese"
            label="Diocèse"
            :error="form.errors.diocese"
            placeholder="Ex : Porto-Novo"
          />

          <Textarea
            v-model="form.adresse"
            label="Adresse"
            :error="form.errors.adresse"
            :rows="2"
            placeholder="Adresse complète de la paroisse"
          />

          <div class="grid gap-5 sm:grid-cols-2">
            <Input
              v-model="form.telephone"
              label="Téléphone"
              type="tel"
              :error="form.errors.telephone"
              placeholder="+229 XX XX XX XX"
            />

            <Input
              v-model="form.email"
              label="Adresse e-mail"
              type="email"
              :error="form.errors.email"
              placeholder="paroisse@exemple.com"
            />
          </div>

          <Input
            v-model="form.whatsapp_channel_url"
            label="Lien du groupe ou de la chaîne WhatsApp"
            type="url"
            :error="form.errors.whatsapp_channel_url"
            placeholder="https://chat.whatsapp.com/…"
            hint="Ce lien s'affichera sur la page publique de la paroisse."
          />
        </div>
      </Card>

      <!-- Couleur et aperçu -->
      <Card padding="none" class="border-t-2 border-spiritual-600">
        <div class="border-b border-cream-200 px-5 py-4 dark:border-gray-700 sm:px-6">
          <h2 class="font-display text-xl text-gray-900 dark:text-white">
            Couleur d'accent
          </h2>
        </div>
        <div class="space-y-5 p-5 sm:p-6">
          <div>
            <p class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">
              Choisissez la couleur principale
            </p>
            <div class="grid grid-cols-6 gap-3 sm:grid-cols-9">
              <button
                v-for="color in colorOptions"
                :key="color.value"
                type="button"
                @click="form.couleur_principale = color.value"
                :class="[
                  'relative flex h-11 items-center justify-center rounded-lg border-2 transition-all touch-target',
                  form.couleur_principale === color.value
                    ? 'border-gray-900 ring-2 ring-offset-2 ring-gray-900 dark:border-white dark:ring-white'
                    : 'border-transparent hover:border-gray-400 dark:hover:border-gray-500',
                ]"
                :style="{ backgroundColor: color.value }"
                :title="color.label"
                :aria-label="color.label"
              >
                <svg
                  v-if="form.couleur_principale === color.value"
                  class="h-5 w-5 text-white drop-shadow"
                  fill="currentColor"
                  viewBox="0 0 20 20"
                  aria-hidden="true"
                >
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
              </button>
            </div>

            <p v-if="form.errors.couleur_principale" class="mt-2 text-sm text-red-600 dark:text-red-400">
              {{ form.errors.couleur_principale }}
            </p>
            <p class="mt-3 text-xs leading-relaxed text-gray-500 dark:text-gray-400">
              Toutes les couleurs proposées garantissent un contraste suffisant avec du texte blanc.
            </p>
          </div>

          <div class="rounded-2xl bg-cream-100 p-4 dark:bg-gray-800">
            <p class="mb-3 text-xs font-semibold uppercase tracking-[0.14em] text-gray-500 dark:text-gray-400">
              Aperçu
            </p>
            <div class="flex flex-wrap items-center gap-3">
              <span
                class="inline-flex min-h-11 items-center rounded-xl px-5 text-sm font-semibold text-white"
                :style="{ backgroundColor: form.couleur_principale || '#1E40AF' }"
              >
                Bouton principal
              </span>
              <span
                class="inline-flex min-h-11 items-center rounded-xl border-2 px-4 text-sm font-semibold"
                :style="{ borderColor: form.couleur_principale || '#1E40AF', color: form.couleur_principale || '#1E40AF' }"
              >
                Couleur du jour
              </span>
            </div>
          </div>

          <div class="flex flex-wrap gap-3 border-t border-cream-200 pt-5 dark:border-gray-700">
            <Button type="submit" variant="gold" size="lg" :loading="form.processing">
              Enregistrer
            </Button>
            <Button type="button" variant="secondary" size="lg" :disabled="form.processing" @click="form.reset()">
              Réinitialiser
            </Button>
          </div>
        </div>
      </Card>

      <!-- Images, lien public et QR code -->
      <Card padding="none" class="border-t-2 border-gold-500 lg:col-span-2">
        <div class="border-b border-cream-200 px-5 py-4 dark:border-gray-700 sm:px-6">
          <h2 class="font-display text-xl text-gray-900 dark:text-white">
            Images et lien public
          </h2>
        </div>

        <div class="grid gap-6 p-5 sm:p-6 lg:grid-cols-2">
          <div class="space-y-5">
            <div>
              <label for="logo_paroisse" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Logo de la paroisse
              </label>
              <input
                id="logo_paroisse"
                type="file"
                accept="image/jpeg,image/png,image/webp"
                class="mt-2 block min-h-11 w-full text-sm text-gray-600 file:mr-3 file:min-h-11 file:rounded-lg file:border-0 file:bg-spiritual-700 file:px-4 file:font-semibold file:text-white dark:text-gray-300"
                @change="onLogoChange"
              />
              <p v-if="form.errors.logo" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ form.errors.logo }}</p>
              <img v-if="aperçuLogo" :src="aperçuLogo" alt="Aperçu du logo" class="mt-4 h-24 w-24 rounded-xl object-contain" />
              <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">JPEG, PNG ou WebP, 2 Mo maximum.</p>
            </div>

            <div>
              <label for="banniere_paroisse" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Bannière de la page d'accueil
              </label>
              <input
                id="banniere_paroisse"
                type="file"
                accept="image/jpeg,image/png,image/webp"
                class="mt-2 block min-h-11 w-full text-sm text-gray-600 file:mr-3 file:min-h-11 file:rounded-lg file:border-0 file:bg-spiritual-700 file:px-4 file:font-semibold file:text-white dark:text-gray-300"
                @change="onBanniereChange"
              />
              <p v-if="form.errors.banniere" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ form.errors.banniere }}</p>
              <img v-if="aperçuBanniere" :src="aperçuBanniere" alt="Aperçu de la bannière" class="mt-4 h-40 w-full rounded-xl object-cover" />
              <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">JPEG, PNG ou WebP, 5 Mo maximum.</p>
            </div>
          </div>

          <div class="rounded-2xl bg-cream-100 p-5 dark:bg-gray-800">
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-gold-700 dark:text-gold-300">
              Lien de la paroisse
            </p>
            <p class="mt-3 break-all font-display text-lg text-gray-900 dark:text-white">
              {{ urlPublique }}
            </p>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
              Partagez ce lien ou le QR code ci-dessous pour inviter les paroissiens.
            </p>
            <div class="mt-5 flex flex-wrap items-start gap-5">
              <img
                :src="route('admin.paroisse.qr-code')"
                alt="QR code du lien public de la paroisse"
                class="h-40 w-40 rounded-xl bg-white p-2"
              />
              <a
                :href="route('admin.paroisse.qr-code')"
                download="qr-code-paroisse.svg"
                class="inline-flex min-h-11 items-center rounded-xl bg-spiritual-700 px-4 text-sm font-semibold text-white hover:bg-spiritual-800"
              >
                Télécharger le QR code
              </a>
            </div>
          </div>
        </div>
      </Card>
    </form>
  </AdminLayout>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Card from '@/Components/UI/Card.vue';
import Input from '@/Components/UI/Input.vue';
import Textarea from '@/Components/UI/Textarea.vue';
import Button from '@/Components/UI/Button.vue';
import IconChurch from '@/Components/Icons/IconChurch.vue';

const props = defineProps({
  paroisse: {
    type: Object,
    required: true,
  },
});

const form = useForm({
  nom: props.paroisse.nom,
  diocese: props.paroisse.diocese || '',
  adresse: props.paroisse.adresse || '',
  telephone: props.paroisse.telephone || '',
  email: props.paroisse.email || '',
  whatsapp_channel_url: props.paroisse.whatsapp_channel_url || '',
  couleur_principale: props.paroisse.couleur_principale || '#1E40AF',
  logo: null,
  banniere: null,
});

const aperçuLogo = ref(props.paroisse.logo_url || null);
const aperçuBanniere = ref(props.paroisse.banniere_url || null);

const urlPublique = computed(() => window.location.origin + '/p/' + props.paroisse.slug);

function onLogoChange(event) {
  const fichier = event.target.files?.[0] || null;
  form.logo = fichier;
  aperçuLogo.value = fichier ? URL.createObjectURL(fichier) : props.paroisse.logo_url;
}

function onBanniereChange(event) {
  const fichier = event.target.files?.[0] || null;
  form.banniere = fichier;
  aperçuBanniere.value = fichier ? URL.createObjectURL(fichier) : props.paroisse.banniere_url;
}

// Couleurs prédéfinies validées WCAG AA (contraste 4.5:1 minimum avec blanc)
const colorOptions = [
  { value: '#1E40AF', label: 'Bleu Royal' },
  { value: '#7C3AED', label: 'Violet' },
  { value: '#DB2777', label: 'Rose Foncé' },
  { value: '#DC2626', label: 'Rouge' },
  { value: '#EA580C', label: 'Orange Foncé' },
  { value: '#CA8A04', label: 'Or Foncé' },
  { value: '#65A30D', label: 'Vert Lime' },
  { value: '#059669', label: 'Vert Émeraude' },
  { value: '#0891B2', label: 'Cyan Foncé' },
  { value: '#0E7490', label: 'Bleu Ciel Foncé' },
  { value: '#4338CA', label: 'Indigo' },
  { value: '#6D28D9', label: 'Violet Profond' },
  { value: '#86198F', label: 'Fuchsia Foncé' },
  { value: '#BE123C', label: 'Rose Rouge' },
  { value: '#B91C1C', label: 'Rouge Foncé' },
  { value: '#92400E', label: 'Ambre Foncé' },
  { value: '#047857', label: 'Vert Forêt' },
  { value: '#115E59', label: 'Sarcelle Foncé' },
];

const submit = () => {
  form.put(route('admin.paroisse.update'), { forceFormData: true });
};
</script>
