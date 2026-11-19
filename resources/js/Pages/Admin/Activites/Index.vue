<template>
  <Head title="Activité de la paroisse" />

  <AdminLayout>
    <PageHeader
      eyebrow="Suivi"
      title="Activité"
      :description="peut_voir_toutes_les_paroisses
        ? 'Vue d’ensemble des utilisateurs et des actions sur toutes les paroisses.'
        : 'Utilisateurs et actions de votre paroisse.'"
    />

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 sm:gap-5">
      <StatCard title="Utilisateurs" :value="stats.utilisateurs" icon="chart" color="spiritual" />
      <StatCard title="Actions aujourd'hui" :value="stats.actions_aujourdhui" icon="clock" color="gold" />
      <StatCard title="Actions sur 7 jours" :value="stats.actions_7_jours" icon="document" color="green" />
    </div>

    <div class="mt-6 grid gap-5 sm:gap-6 lg:grid-cols-2">
      <Card padding="none" class="border-t-2 border-spiritual-600">
        <div class="border-b border-cream-200 px-5 py-4 dark:border-gray-700 sm:px-6">
          <h2 class="font-display text-xl text-gray-900 dark:text-white">Dernières actions</h2>
        </div>

        <ul v-if="activites.data.length" class="divide-y divide-cream-200 dark:divide-gray-700">
          <li v-for="activite in activites.data" :key="activite.id" class="flex flex-wrap items-center gap-3 px-5 py-4 sm:px-6">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-cream-200 text-spiritual-700 dark:bg-gray-800 dark:text-spiritual-300">
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </span>
            <div class="min-w-0 flex-1">
              <p class="font-medium text-gray-900 dark:text-white">{{ activite.label }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">
                {{ activite.utilisateur }} · {{ activite.cible_type || '—' }}
              </p>
            </div>
            <span class="text-xs text-gray-500 dark:text-gray-400">{{ activite.created_at }}</span>
          </li>
        </ul>
        <p v-else class="p-6 text-sm text-gray-500 dark:text-gray-400">Aucune action enregistrée pour le moment.</p>

        <nav v-if="activites.last_page > 1" class="flex items-center justify-between gap-3 border-t border-cream-200 px-5 py-4 text-sm dark:border-gray-700 sm:px-6" aria-label="Pagination">
          <Link v-if="activites.prev_page_url" :href="activites.prev_page_url" class="inline-flex min-h-11 items-center rounded-lg px-3 font-semibold text-spiritual-700 dark:text-spiritual-300">Précédent</Link>
          <span class="text-gray-500">Page {{ activites.current_page }} sur {{ activites.last_page }}</span>
          <Link v-if="activites.next_page_url" :href="activites.next_page_url" class="inline-flex min-h-11 items-center rounded-lg px-3 font-semibold text-spiritual-700 dark:text-spiritual-300">Suivant</Link>
        </nav>
      </Card>

      <Card padding="none" class="border-t-2 border-gold-500">
        <div class="border-b border-cream-200 px-5 py-4 dark:border-gray-700 sm:px-6">
          <h2 class="font-display text-xl text-gray-900 dark:text-white">Utilisateurs</h2>
        </div>
        <ul v-if="utilisateurs.length" class="divide-y divide-cream-200 dark:divide-gray-700">
          <li v-for="utilisateur in utilisateurs" :key="utilisateur.id" class="flex flex-wrap items-center gap-3 px-5 py-4 sm:px-6">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-spiritual-700 text-sm font-semibold text-white">
              {{ utilisateur.name.charAt(0).toUpperCase() }}
            </span>
            <div class="min-w-0 flex-1">
              <p class="truncate font-medium text-gray-900 dark:text-white">{{ utilisateur.name }}</p>
              <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ utilisateur.email }}</p>
            </div>
            <span class="text-xs font-semibold text-gold-700 dark:text-gold-300">{{ utilisateur.role_label }}</span>
          </li>
        </ul>
        <p v-else class="p-6 text-sm text-gray-500 dark:text-gray-400">Aucun utilisateur.</p>
      </Card>
    </div>

    <p class="mt-6 text-center text-xs text-gray-500 dark:text-gray-400">
      Le journal enregistre uniquement le type d'action, son auteur et sa cible — jamais de nom de membre, de téléphone ni d'adresse IP.
    </p>
  </AdminLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Card from '@/Components/UI/Card.vue';
import StatCard from '@/Components/Admin/StatCard.vue';

defineProps({
  activites: { type: Object, required: true },
  utilisateurs: { type: Array, default: () => [] },
  stats: { type: Object, required: true },
  peut_voir_toutes_les_paroisses: { type: Boolean, default: false },
});
</script>
