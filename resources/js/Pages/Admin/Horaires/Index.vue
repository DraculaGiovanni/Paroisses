<template>
  <Head title="Horaires" />
  
  <AdminLayout>
    <PageHeader title="Horaires de messe">
      <template #actions>
        <Button
          variant="primary"
          @click="showForm = true; editingHoraire = null"
        >
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Ajouter un horaire
        </Button>
      </template>
    </PageHeader>
    
    <div v-if="horaires?.length > 0" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
      <Card
        v-for="horaire in horaires"
        :key="horaire.id"
      >
        <div class="p-5">
          <div class="flex items-start justify-between mb-3">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-1">
                <h3 class="font-semibold text-gray-900 dark:text-white">
                  {{ horaire.type }}
                </h3>
                <Badge :variant="horaire.actif ? 'success' : 'default'" size="sm">
                  {{ horaire.actif ? 'Actif' : 'Masqué' }}
                </Badge>
              </div>
              <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ JOURS[horaire.jour_semaine] }} · {{ horaire.heure_fin ? `${horaire.heure} – ${horaire.heure_fin}` : horaire.heure }}
              </p>
              <p v-if="horaire.valide_du || horaire.valide_au" class="text-xs text-gray-500 mt-1">
                Période : {{ horaire.valide_du || 'sans début' }} – {{ horaire.valide_au || 'sans fin' }}
              </p>
              <p v-if="horaire.lieu" class="text-sm text-gray-500 mt-1">
                {{ horaire.lieu }}
              </p>
            </div>
            
            <!-- Toggle actif avec mise à jour optimiste -->
            <div class="flex items-center gap-2">
              <button
                @click="toggleActif(horaire)"
                :class="[
                  'relative inline-flex h-6 w-11 items-center rounded-full transition-colors touch-target',
                  horaire.actif ? 'bg-spiritual-600' : 'bg-gray-300 dark:bg-gray-700'
                ]"
                :disabled="horaire.toggling"
              >
                <span
                  :class="[
                    'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                    horaire.actif ? 'translate-x-6' : 'translate-x-1'
                  ]"
                />
              </button>
            </div>
          </div>
          
          <div class="flex items-center gap-2">
            <Button
              variant="secondary"
              size="sm"
              @click="editHoraire(horaire)"
            >
              Modifier
            </Button>
            
            <Button
              variant="danger"
              size="sm"
              @click="deleteHoraire(horaire)"
            >
              Supprimer
            </Button>
          </div>
        </div>
      </Card>
    </div>
    
    <EmptyState
      v-else
      title="Aucun horaire"
      description="Ajoutez les horaires de messe de votre paroisse."
    >
      <template #action>
        <Button
          variant="primary"
          @click="showForm = true"
        >
          Ajouter un horaire
        </Button>
      </template>
    </EmptyState>
    
    <!-- Modal formulaire -->
    <Modal v-model:show="showForm" :title="editingHoraire ? 'Modifier l\'horaire' : 'Nouvel horaire'" size="md">
      <form @submit.prevent="submitForm" class="space-y-4">
        <Select
          v-model="form.type"
          label="Type de célébration"
          :options="[
            { value: 'messe', label: 'Messe' },
            { value: 'confession', label: 'Confession' },
            { value: 'adoration', label: 'Adoration' },
            { value: 'autre', label: 'Autre' }
          ]"
          :error="form.errors.type"
          required
        />
        
        <Select
          v-model.number="form.jour_semaine"
          label="Jour"
          :options="[
            { value: 1, label: 'Lundi' },
            { value: 2, label: 'Mardi' },
            { value: 3, label: 'Mercredi' },
            { value: 4, label: 'Jeudi' },
            { value: 5, label: 'Vendredi' },
            { value: 6, label: 'Samedi' },
            { value: 7, label: 'Dimanche' }
          ]"
          :error="form.errors.jour_semaine"
          required
        />
        
        <Input
          v-model="form.heure"
          type="time"
          label="Heure"
          :error="form.errors.heure"
          required
        />

        <Input
          v-model="form.heure_fin"
          type="time"
          label="Heure de fin (optionnel)"
          :error="form.errors.heure_fin"
        />

        <div class="grid gap-4 sm:grid-cols-2">
          <Input
            v-model="form.valide_du"
            type="date"
            label="Valide à partir du"
            :error="form.errors.valide_du"
          />
          <Input
            v-model="form.valide_au"
            type="date"
            label="Valide jusqu’au"
            :error="form.errors.valide_au"
          />
        </div>
        
        <Input
          v-model="form.lieu"
          label="Lieu (optionnel)"
          :error="form.errors.lieu"
          placeholder="Ex: Église principale"
        />
        
        <Textarea
          v-model="form.note"
          label="Note (optionnel)"
          :rows="2"
          :error="form.errors.note"
          placeholder="Informations complémentaires..."
        />
      </form>
      
      <template #footer>
        <Button variant="secondary" @click="showForm = false">Annuler</Button>
        <Button variant="primary" :loading="form.processing" @click="submitForm">
          {{ editingHoraire ? 'Mettre à jour' : 'Créer' }}
        </Button>
      </template>
    </Modal>
    
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
import { ref } from 'vue';
import { useForm, Head, router } from '@inertiajs/vue3';
import { useToast } from '@/Composables/useToast';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';
import Badge from '@/Components/UI/Badge.vue';
import Modal from '@/Components/UI/Modal.vue';
import Input from '@/Components/UI/Input.vue';
import Textarea from '@/Components/UI/Textarea.vue';
import Select from '@/Components/UI/Select.vue';
import EmptyState from '@/Components/UI/EmptyState.vue';
import ConfirmDialog from '@/Components/UI/ConfirmDialog.vue';

const props = defineProps({
  horaires: {
    type: Array,
    default: () => [],
  },
});

const { success, error } = useToast();
const showForm = ref(false);
const editingHoraire = ref(null);
const confirmDialog = ref({ show: false, title: '', message: '', onConfirm: () => {} });

const JOURS = ['', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];

const form = useForm({
  type: 'messe',
  jour_semaine: 7,
  heure: '10:00',
  heure_fin: '',
  valide_du: '',
  valide_au: '',
  lieu: '',
  note: '',
  actif: true,
});

const editHoraire = (horaire) => {
  editingHoraire.value = horaire;
  form.type = horaire.type;
  form.jour_semaine = horaire.jour_semaine;
  form.heure = horaire.heure;
  form.heure_fin = horaire.heure_fin || '';
  form.valide_du = horaire.valide_du || '';
  form.valide_au = horaire.valide_au || '';
  form.lieu = horaire.lieu || '';
  form.note = horaire.note || '';
  form.actif = horaire.actif;
  showForm.value = true;
};

const submitForm = () => {
  const url = editingHoraire.value
    ? route('admin.horaires.update', editingHoraire.value.id)
    : route('admin.horaires.store');
    
  const method = editingHoraire.value ? 'put' : 'post';
  
  form[method](url, {
    onSuccess: () => {
      showForm.value = false;
      form.reset();
      editingHoraire.value = null;
    },
  });
};

// Toggle actif avec mise à jour optimiste
const toggleActif = (horaire) => {
  const oldValue = horaire.actif;
  horaire.actif = !oldValue; // Mise à jour optimiste
  horaire.toggling = true;
  
  router.put(route('admin.horaires.update', horaire.id), {
    ...horaire,
    actif: horaire.actif,
  }, {
    preserveScroll: true,
    onError: () => {
      // Rollback en cas d'erreur
      horaire.actif = oldValue;
      error('Erreur lors de la mise à jour de l\'horaire');
    },
    onFinish: () => {
      horaire.toggling = false;
    },
  });
};

const deleteHoraire = (horaire) => {
  confirmDialog.value = {
    show: true,
    title: 'Supprimer l\'horaire',
    message: `Supprimer l'horaire du ${JOURS[horaire.jour_semaine]} à ${horaire.heure} ?`,
    onConfirm: () => {
      router.delete(route('admin.horaires.destroy', horaire.id), {
        preserveScroll: true,
        onFinish: () => confirmDialog.value.show = false,
      });
    },
  };
};
</script>
