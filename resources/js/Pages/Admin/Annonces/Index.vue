<template>
  <Head title="Annonces" />
  
  <AdminLayout>
    <PageHeader title="Annonces">
      <template #actions>
        <Button
          :href="route('admin.annonces.create')"
          variant="primary"
        >
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Nouvelle annonce
        </Button>
      </template>
    </PageHeader>
    
    <div class="mb-4 grid gap-3 sm:grid-cols-2">
      <Select
        v-model="filters.statut"
        label="Statut"
        :options="statusOptions"
        @update:model-value="applyFilters"
      />
      <Select
        v-model="filters.categorie"
        label="Catégorie"
        :options="categoryOptions"
        @update:model-value="applyFilters"
      />
    </div>

    <div v-if="loading" class="space-y-4">
      <Skeleton v-for="i in 3" :key="i" height="100px" />
    </div>
    
    <div v-else-if="annonces?.data?.length > 0" class="grid gap-4 xl:grid-cols-2">
      <Card
        v-for="annonce in annonces?.data || []"
        :key="annonce.id"
        hover
      >
        <div class="p-6">
          <div class="flex items-start justify-between gap-4">
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 mb-2">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white truncate">
                  {{ annonce.titre }}
                </h3>
                <Badge v-if="annonce.epinglee" variant="warning" size="sm">
                  Épinglée
                </Badge>
                <Badge v-if="!annonce.publie_le" variant="default" size="sm">
                  Brouillon
                </Badge>
                <Badge v-else variant="success" size="sm">
                  Publiée
                </Badge>
              </div>
              
              <p class="text-gray-600 dark:text-gray-400 line-clamp-2 mb-3">
                {{ annonce.contenu }}
              </p>
              
              <p class="text-sm text-gray-500 dark:text-gray-500">
                {{ formatDate(annonce.publie_le || annonce.created_at) }}
              </p>
            </div>
            
            <div class="flex items-center gap-2">
              <Button
                :href="route('admin.annonces.edit', annonce.id)"
                variant="secondary"
                size="sm"
              >
                Modifier
              </Button>
              
              <Button
                variant="danger"
                size="sm"
                @click="deleteAnnonce(annonce)"
              >
                Supprimer
              </Button>

              <Button
                variant="secondary"
                size="sm"
                @click="toggleArchive(annonce)"
              >
                {{ annonce.archivee_le ? 'Désarchiver' : 'Archiver' }}
              </Button>
            </div>
          </div>
        </div>
      </Card>
      
      <!-- Pagination simple -->
      <div v-if="annonces?.next_page_url || annonces?.prev_page_url" class="flex justify-between items-center pt-4">
        <Button
          v-if="annonces?.prev_page_url"
          :href="annonces.prev_page_url"
          variant="secondary"
          preserve-scroll
        >
          Précédent
        </Button>
        <div v-else></div>
        
        <Button
          v-if="annonces?.next_page_url"
          :href="annonces.next_page_url"
          variant="secondary"
          preserve-scroll
        >
          Suivant
        </Button>
      </div>
    </div>
    
    <EmptyState
      v-else
      title="Aucune annonce"
      description="Commencez par créer votre première annonce."
    >
      <template #action>
        <Button
          :href="route('admin.annonces.create')"
          variant="primary"
        >
          Créer une annonce
        </Button>
      </template>
    </EmptyState>
    
    <ConfirmDialog
      v-model:show="confirmDialog.show"
      :title="confirmDialog.title"
      :message="confirmDialog.message"
      variant="danger"
      @confirm="confirmDialog.onConfirm"
      @cancel="confirmDialog.show = false"
    />
  </AdminLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';
import Badge from '@/Components/UI/Badge.vue';
import Skeleton from '@/Components/UI/Skeleton.vue';
import EmptyState from '@/Components/UI/EmptyState.vue';
import ConfirmDialog from '@/Components/UI/ConfirmDialog.vue';
import Select from '@/Components/UI/Select.vue';

const props = defineProps({
  annonces: {
    type: Object,
    default: () => ({ data: [], links: [], meta: {} }),
  },
  categories: {
    type: Array,
    default: () => [],
  },
  filters: {
    type: Object,
    default: () => ({ statut: '', categorie: '' }),
  },
});

const statusOptions = [
  { value: '', label: 'Tous les statuts' },
  { value: 'brouillon', label: 'Brouillon' },
  { value: 'programmee', label: 'Programmé' },
  { value: 'publiee', label: 'Publiée' },
  { value: 'expiree', label: 'Expirée' },
  { value: 'archivee', label: 'Archivée' },
];

const categoryOptions = [
  { value: '', label: 'Toutes les catégories' },
  ...(props.categories || []).map((category) => ({
    value: category.value,
    label: category.label,
  })),
];

const filters = reactive({
  statut: props.filters?.statut || '',
  categorie: props.filters?.categorie || '',
});

const applyFilters = () => {
  router.get(route('admin.annonces.index'), {
    statut: filters.statut || undefined,
    categorie: filters.categorie || undefined,
  }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const loading = ref(false);
const confirmDialog = ref({
  show: false,
  title: '',
  message: '',
  onConfirm: () => {},
});

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  });
};

const deleteAnnonce = (annonce) => {
  confirmDialog.value = {
    show: true,
    title: 'Supprimer l\'annonce',
    message: `Êtes-vous sûr de vouloir supprimer "${annonce.titre}" ? Cette action est irréversible.`,
    onConfirm: () => {
      router.delete(route('admin.annonces.destroy', annonce.id), {
        preserveScroll: true,
        onStart: () => loading.value = true,
        onFinish: () => {
          loading.value = false;
          confirmDialog.value.show = false;
        },
      });
    },
  };
};

const toggleArchive = (annonce) => {
  router.patch(route('admin.annonces.toggle-archive', annonce.id), {}, {
    preserveScroll: true,
  });
};
</script>
