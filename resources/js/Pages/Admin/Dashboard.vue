<template>
  <Head title="Tableau de bord" />

  <AdminLayout>
    <PageHeader
      eyebrow="Vue d'ensemble"
      title="Tableau de bord"
      :description="$page.props.paroisse ? `Activité de ${$page.props.paroisse.nom}` : 'Bienvenue dans l’administration'"
    />

    <!-- Chiffres clés : une seule information par carte -->
    <div class="grid grid-cols-2 gap-3 sm:gap-5 lg:grid-cols-4">
      <StatCard
        title="Annonces publiées"
        :value="stats.annonces_publiees"
        icon="megaphone"
        color="spiritual"
      />
      <StatCard
        title="Programmées"
        :value="stats.annonces_programmees"
        icon="clock"
        color="gold"
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

    <div class="mt-6 grid gap-5 sm:gap-6 lg:grid-cols-2">
      <!-- Prochaine messe : bloc le plus visible -->
      <Card padding="none" class="border-t-2 border-gold-500">
        <div class="flex items-center justify-between gap-4 px-5 py-4 sm:px-6">
          <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gold-700 dark:text-gold-300">
            Prochaine messe
          </p>
          <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-gold-100 text-gold-700 dark:bg-gold-900/30 dark:text-gold-300">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </span>
        </div>

        <div v-if="prochaine_messe" class="flex flex-col gap-5 border-t border-cream-200 px-5 py-6 dark:border-gray-700 sm:flex-row sm:items-center sm:px-6">
          <div class="flex shrink-0 flex-row items-center gap-3 rounded-2xl bg-spiritual-700 px-4 py-3 text-white sm:w-24 sm:flex-col sm:gap-0">
            <span class="text-xs font-semibold uppercase tracking-wide text-spiritual-100">
              {{ jourLabel(prochaine_messe.jour_semaine) }}
            </span>
            <span class="font-display text-2xl leading-none">{{ prochaine_messe.heure }}</span>
          </div>
          <div class="min-w-0">
            <p class="font-display text-2xl text-gray-900 dark:text-white sm:text-3xl">
              {{ prochaine_messe.type || 'Messe' }}
            </p>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
              <span class="font-semibold text-gray-900 dark:text-white">{{ prochaine_messe.heure_fin ? `${prochaine_messe.heure} – ${prochaine_messe.heure_fin}` : prochaine_messe.heure }}</span>
              <span v-if="prochaine_messe.lieu"> · {{ prochaine_messe.lieu }}</span>
            </p>
          </div>
        </div>

        <div v-else class="border-t border-cream-200 px-5 py-6 dark:border-gray-700 sm:px-6">
          <EmptyState
            title="Aucune messe programmée"
            description="Ajoutez des horaires pour voir la prochaine messe apparaître ici."
          />
        </div>
      </Card>

      <!-- Actions rapides -->
      <Card padding="none" class="border-t-2 border-spiritual-600">
        <div class="border-b border-cream-200 px-5 py-4 dark:border-gray-700 sm:px-6">
          <p class="text-xs font-semibold uppercase tracking-[0.18em] text-spiritual-700 dark:text-spiritual-300">
            Actions rapides
          </p>
        </div>

        <div class="space-y-3 p-5 sm:p-6">
          <Button
            :href="route('admin.annonces.create')"
            variant="gold"
            size="lg"
            full-width
            class="justify-start"
          >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nouvelle annonce
          </Button>

          <Button
            :href="route('admin.horaires.index')"
            variant="secondary"
            size="lg"
            full-width
            class="justify-start"
          >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Gérer les horaires
          </Button>

          <Button
            v-if="$page.props.paroisse"
            :href="route('paroisse.show', $page.props.paroisse.slug)"
            variant="ghost"
            size="lg"
            full-width
            class="justify-start"
            target="_blank"
          >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
            </svg>
            Voir le site public
          </Button>
        </div>
      </Card>
    </div>

    <p class="mt-8 text-center text-xs text-gray-500 dark:text-gray-400">
      Les chiffres sont calculés à partir des données enregistrées pour votre paroisse.
    </p>
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
