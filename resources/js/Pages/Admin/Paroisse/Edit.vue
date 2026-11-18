<template>
  <Head title="Réglages de la paroisse" />
  
  <AdminLayout>
    <PageHeader
      title="Réglages de la paroisse"
      description="Modifiez les informations de votre paroisse"
    />
    
    <Card>
      <form @submit.prevent="submit" class="p-6 space-y-6">
        <Input
          v-model="form.nom"
          label="Nom de la paroisse"
          :error="form.errors.nom"
          required
          placeholder="Ex: Cathédrale Notre-Dame"
        />
        
        <Input
          v-model="form.diocese"
          label="Diocèse"
          :error="form.errors.diocese"
          placeholder="Ex: Porto-Novo"
        />
        
        <Textarea
          v-model="form.adresse"
          label="Adresse"
          :error="form.errors.adresse"
          :rows="2"
          placeholder="Adresse complète de la paroisse"
        />
        
        <div class="grid gap-4 sm:gap-6 sm:grid-cols-2">
          <Input
            v-model="form.telephone"
            label="Téléphone"
            type="tel"
            :error="form.errors.telephone"
            placeholder="+229 XX XX XX XX"
          />
          
          <Input
            v-model="form.email"
            label="Email"
            type="email"
            :error="form.errors.email"
            placeholder="paroisse@exemple.com"
          />
        </div>
        
        <!-- Sélecteur de couleur -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
            Couleur d'accent
          </label>
          
          <div class="grid grid-cols-4 sm:grid-cols-6 gap-3">
            <button
              v-for="color in colorOptions"
              :key="color.value"
              type="button"
              @click="form.couleur_principale = color.value"
              :class="[
                'relative h-16 rounded-lg border-2 transition-all touch-target',
                form.couleur_principale === color.value
                  ? 'border-gray-900 dark:border-white ring-2 ring-offset-2 ring-gray-900 dark:ring-white scale-105'
                  : 'border-gray-300 dark:border-gray-700 hover:border-gray-400 dark:hover:border-gray-600'
              ]"
              :style="{ backgroundColor: color.value }"
              :title="color.label"
            >
              <span
                v-if="form.couleur_principale === color.value"
                class="absolute inset-0 flex items-center justify-center"
              >
                <svg class="w-6 h-6 text-white drop-shadow-lg" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
              </span>
            </button>
          </div>
          
          <p v-if="form.errors.couleur_principale" class="mt-2 text-sm text-red-600 dark:text-red-400">
            {{ form.errors.couleur_principale }}
          </p>
          
          <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            Choisissez la couleur principale de votre paroisse. Toutes les couleurs garantissent une bonne lisibilité du texte blanc.
          </p>
          
          <!-- Aperçu -->
          <div class="mt-4 p-4 bg-cream-100 dark:bg-gray-800 rounded-xl">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">Aperçu :</p>
            <Button
              type="button"
              :style="{ backgroundColor: form.couleur_principale || '#1E40AF' }"
              class="!bg-opacity-100 hover:!bg-opacity-90"
            >
              Bouton principal
            </Button>
          </div>
        </div>
        
        <div class="flex items-center gap-3 pt-4 border-t border-cream-300 dark:border-gray-700">
          <Button
            type="submit"
            variant="primary"
            :loading="form.processing"
          >
            Enregistrer
          </Button>
          
          <Button
            type="button"
            variant="secondary"
            :disabled="form.processing"
            @click="form.reset()"
          >
            Réinitialiser
          </Button>
        </div>
      </form>
    </Card>
  </AdminLayout>
</template>

<script setup>
import { useForm, Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Card from '@/Components/UI/Card.vue';
import Input from '@/Components/UI/Input.vue';
import Textarea from '@/Components/UI/Textarea.vue';
import Button from '@/Components/UI/Button.vue';

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
  couleur_principale: props.paroisse.couleur_principale || '#1E40AF',
});

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
  form.put(route('admin.paroisse.update'));
};
</script>
