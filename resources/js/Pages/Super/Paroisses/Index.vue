<template>
  <Head title="Gestion des paroisses" />
  
  <AdminLayout>
    <PageHeader
      title="Paroisses"
      description="Gérez toutes les paroisses de la plateforme"
    >
      <template #actions>
        <Button
          variant="primary"
          icon="plus"
          :href="route('super.paroisses.create')"
        >
          Nouvelle paroisse
        </Button>
      </template>
    </PageHeader>
    
    <Card v-if="paroisses?.length > 0">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-cream-100 dark:bg-gray-800">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                Paroisse
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                Diocèse
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider hidden sm:table-cell">
                Utilisateurs
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                Slug
              </th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-900 divide-y divide-cream-200 dark:divide-gray-800">
            <tr
              v-for="paroisse in paroisses"
              :key="paroisse.id"
              class="hover:bg-cream-50 dark:hover:bg-gray-800 transition-colors"
            >
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                  {{ paroisse.nom }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                  {{ paroisse.diocese || '—' }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap hidden sm:table-cell">
                <Badge variant="secondary">
                  {{ paroisse.users_count }} utilisateur{{ paroisse.users_count > 1 ? 's' : '' }}
                </Badge>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <code class="text-xs text-spiritual-600 dark:text-spiritual-400 bg-spiritual-50 dark:bg-spiritual-900/20 px-2 py-1 rounded">
                  {{ paroisse.slug }}
                </code>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </Card>
    
    <EmptyState
      v-else
      icon="church"
      title="Aucune paroisse"
      description="Commencez par créer votre première paroisse"
    >
      <template #actions>
        <Button
          variant="primary"
          :href="route('super.paroisses.create')"
        >
          Créer une paroisse
        </Button>
      </template>
    </EmptyState>
  </AdminLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';
import Badge from '@/Components/UI/Badge.vue';
import EmptyState from '@/Components/UI/EmptyState.vue';

defineProps({
  paroisses: {
    type: Array,
    default: () => [],
  },
});
</script>
