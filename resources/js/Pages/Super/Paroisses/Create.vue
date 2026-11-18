<template>
  <Head title="Créer une paroisse" />
  
  <AdminLayout>
    <PageHeader
      title="Nouvelle paroisse"
      description="Créez une paroisse et son administrateur"
    />
    
    <Card>
      <form @submit.prevent="submit" class="p-6 space-y-8">
        <!-- Informations de la paroisse -->
        <div>
          <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
            Informations de la paroisse
          </h3>
          
          <div class="space-y-6">
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
            
            <!-- Sélecteur de couleur -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                Couleur d'accent (optionnelle)
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
              
              <!-- Aperçu -->
              <div v-if="form.couleur_principale" class="mt-4 p-4 bg-cream-100 dark:bg-gray-800 rounded-xl">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">Aperçu :</p>
                <Button
                  type="button"
                  :style="{ backgroundColor: form.couleur_principale }"
                  class="!bg-opacity-100 hover:!bg-opacity-90"
                >
                  Bouton principal
                </Button>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Séparateur -->
        <div class="border-t border-cream-300 dark:border-gray-700"></div>
        
        <!-- Compte administrateur -->
        <div>
          <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
            Compte administrateur
          </h3>
          
          <div class="space-y-6">
            <Input
              v-model="form.admin_name"
              label="Nom complet"
              :error="form.errors.admin_name"
              required
              placeholder="Ex: Père Jean Dupont"
            />
            
            <Input
              v-model="form.admin_email"
              label="Email"
              type="email"
              :error="form.errors.admin_email"
              required
              placeholder="admin@paroisse.exemple.com"
            />
            
            <Input
              v-model="form.admin_password"
              label="Mot de passe"
              type="password"
              :error="form.errors.admin_password"
              required
              placeholder="••••••••"
              hint="Minimum 8 caractères"
            />
          </div>
        </div>
        
        <!-- Actions -->
        <div class="flex items-center gap-3 pt-4 border-t border-cream-300 dark:border-gray-700">
          <Button
            type="submit"
            variant="primary"
            :loading="form.processing"
          >
            Créer la paroisse
          </Button>
          
          <Button
            type="button"
            variant="secondary"
            :disabled="form.processing"
            :href="route('super.paroisses.index')"
          >
            Annuler
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
import Button from '@/Components/UI/Button.vue';

const form = useForm({
  nom: '',
  diocese: '',
  couleur_principale: '#1E40AF',
  admin_name: '',
  admin_email: '',
  admin_password: '',
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
  form.post(route('super.paroisses.store'), {
    preserveScroll: true,
  });
};
</script>
