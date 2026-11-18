<template>
  <Head :title="annonce ? 'Modifier l\'annonce' : 'Nouvelle annonce'" />
  
  <AdminLayout>
    <PageHeader
      :title="annonce ? 'Modifier l\'annonce' : 'Nouvelle annonce'"
      :description="annonce ? `Modification de : ${annonce.titre}` : 'Créer une nouvelle annonce pour votre paroisse'"
    >
      <template #actions>
        <Button
          v-if="annonce"
          as="link"
          :href="route('paroisse.show', $page.props.paroisse.slug)"
          variant="ghost"
          size="sm"
          target="_blank"
        >
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
          </svg>
          Voir sur le site
        </Button>
      </template>
    </PageHeader>
    
    <Card>
      <form @submit.prevent="submit" class="p-6 space-y-6">
        <Input
          v-model="form.titre"
          label="Titre"
          :error="form.errors.titre"
          required
          placeholder="Ex: Messe de Noël"
        />
        
        <Textarea
          v-model="form.contenu"
          label="Contenu"
          :error="form.errors.contenu"
          :rows="8"
          :maxlength="5000"
          required
          placeholder="Décrivez votre annonce..."
        />

        <div class="grid gap-4 sm:gap-6 sm:grid-cols-2">
          <Select
            v-model="form.categorie"
            label="Catégorie"
            :options="categoryOptions"
            :error="form.errors.categorie"
            required
          />

          <Input
            v-model="form.responsable"
            label="Responsable (optionnel)"
            :error="form.errors.responsable"
            placeholder="Ex: Secrétariat paroissial"
          />
        </div>

        <div class="grid gap-4 sm:gap-6 sm:grid-cols-2">
          <Input
            v-model="form.publie_le"
            type="datetime-local"
            label="Publication programmée"
            :error="form.errors.publie_le"
          />

          <Input
            v-model="form.expire_le"
            type="datetime-local"
            label="Date de fin (optionnel)"
            :error="form.errors.expire_le"
          />
        </div>
        
        <div class="grid gap-4 sm:gap-6 sm:grid-cols-2">
          <div class="flex items-center gap-3 p-4 bg-cream-100 dark:bg-gray-800 rounded-xl">
            <input
              id="epinglee"
              v-model="form.epinglee"
              type="checkbox"
              class="w-5 h-5 text-spiritual-700 border-cream-300 rounded focus:ring-spiritual-600"
            />
            <label for="epinglee" class="text-sm font-medium text-gray-900 dark:text-white cursor-pointer">
              Épingler cette annonce
            </label>
          </div>
          
          <div class="flex items-center gap-3 p-4 bg-cream-100 dark:bg-gray-800 rounded-xl">
            <input
              id="publier"
              v-model="form.publier"
              type="checkbox"
              class="w-5 h-5 text-spiritual-700 border-cream-300 rounded focus:ring-spiritual-600"
            />
            <label for="publier" class="text-sm font-medium text-gray-900 dark:text-white cursor-pointer">
              Publier immédiatement
            </label>
          </div>

          <div class="flex items-center gap-3 p-4 bg-cream-100 dark:bg-gray-800 rounded-xl">
            <input
              id="notifier"
              v-model="form.notifier"
              type="checkbox"
              class="w-5 h-5 text-spiritual-700 border-cream-300 rounded focus:ring-spiritual-600"
            />
            <label for="notifier" class="text-sm font-medium text-gray-900 dark:text-white cursor-pointer">
              Notifier les abonnés
            </label>
          </div>
        </div>
        
        <div class="flex items-center gap-3 pt-4 border-t border-cream-300 dark:border-gray-700">
          <Button
            type="submit"
            variant="primary"
            :loading="form.processing"
          >
            {{ annonce ? 'Mettre à jour' : 'Créer l\'annonce' }}
          </Button>
          
          <Button
            as="link"
            :href="route('admin.annonces.index')"
            variant="secondary"
            :disabled="form.processing"
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
import Textarea from '@/Components/UI/Textarea.vue';
import Button from '@/Components/UI/Button.vue';
import Select from '@/Components/UI/Select.vue';

const props = defineProps({
  annonce: Object,
  categories: {
    type: Array,
    default: () => [],
  },
});

const categoryOptions = (props.categories || []).map((category) => ({
  value: category.value,
  label: category.label,
}));

const form = useForm({
  titre: props.annonce?.titre || '',
  contenu: props.annonce?.contenu || '',
  epinglee: props.annonce?.epinglee || false,
  publier: props.annonce?.publie_le ? true : false,
  publie_le: props.annonce?.publie_le || '',
  expire_le: props.annonce?.expire_le || '',
  categorie: props.annonce?.categorie || 'administration',
  responsable: props.annonce?.responsable || '',
  notifier: props.annonce?.notifier ?? true,
});

const submit = () => {
  if (props.annonce) {
    form.put(route('admin.annonces.update', props.annonce.id));
  } else {
    form.post(route('admin.annonces.store'));
  }
};
</script>
