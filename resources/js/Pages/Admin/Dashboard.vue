<template>
  <Head title="Tableau de bord" />
  
  <AdminLayout>
    <PageHeader 
      title="Tableau de bord"
      description="Vue d'ensemble de votre paroisse"
    />
    
    <!-- Statistiques -->
    <div class="mb-8 grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4">
      <StatCard
        title="Annonces publiées"
        :value="stats.annonces_publiees"
        icon="megaphone"
        color="spiritual"
      />
      
      <StatCard
        title="Programmées"
        :value="stats.annonces_programmees"
        icon="calendar"
        color="spiritual"
      />
      
      <StatCard
        title="Brouillons"
        :value="stats.annonces_brouillons"
        icon="document"
        color="gold"
      />
      
      <StatCard
        title="Horaires actifs"
        :value="stats.horaires_actifs"
        icon="clock"
        color="green"
      />
    </div>
    
    <!-- Prochaine messe & Actions rapides -->
    <div class="grid gap-4 sm:gap-6 lg:grid-cols-2">
      <!-- Prochaine messe -->
      <Card v-if="prochaine_messe">
        <template #header>
          <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-gold-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="font-semibold">Prochaine messe</h3>
          </div>
        </template>
        
        <div class="space-y-2">
          <p class="text-lg font-semibold text-spiritual-700 dark:text-spiritual-400">
            {{ jourLabel(prochaine_messe.jour_semaine) }}
          </p>
          <p class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ prochaine_messe.heure }}
          </p>
          <p v-if="prochaine_messe.lieu" class="text-gray-600 dark:text-gray-400">
            {{ prochaine_messe.lieu }}
          </p>
        </div>
      </Card>
      
      <EmptyState
        v-else
        title="Aucune messe programmée"
        description="Ajoutez des horaires de messe pour voir la prochaine ici."
      />
      
      <!-- Actions rapides -->
      <Card>
        <template #header>
          <h3 class="font-semibold">Actions rapides</h3>
        </template>
        
        <div class="space-y-3">
          <Button
            :href="route('admin.annonces.create')"
            variant="primary"
            full-width
            class="justify-start"
          >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nouvelle annonce
          </Button>
          
          <Button
            :href="route('admin.horaires.index')"
            variant="secondary"
            full-width
            class="justify-start"
          >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Gérer les horaires
          </Button>
          
          <Button
            v-if="$page.props.paroisse"
            :href="route('paroisse.show', $page.props.paroisse.slug)"
            variant="ghost"
            full-width
            class="justify-start"
            target="_blank"
          >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
            </svg>
            Voir le site public
          </Button>
        </div>
      </Card>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';
import EmptyState from '@/Components/UI/EmptyState.vue';
import StatCard from '@/Components/Admin/StatCard.vue';

defineProps({
  stats: {
    type: Object,
    default: () => ({
      annonces_publiees: 0,
      annonces_programmees: 0,
      annonces_brouillons: 0,
      horaires_actifs: 0,
    }),
  },
  prochaine_messe: {
    type: Object,
    default: null,
  },
});

const JOURS = ['', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];

const jourLabel = (jour) => JOURS[jour] || '';
</script>
