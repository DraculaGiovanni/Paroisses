<template>
  <Head title="Avis anonymes" />

  <AdminLayout>
    <PageHeader
      eyebrow="Écoute"
      title="Avis anonymes"
      description="Modérez les avis laissés par les paroissiens. Aucun nom ni adresse IP n'est enregistré."
    />

    <div class="mb-6 flex flex-wrap gap-2" aria-label="Filtrer les avis">
      <Link
        v-for="item in filtres"
        :key="item.value"
        :href="item.href"
        :class="[
          'inline-flex min-h-11 items-center rounded-full border px-4 text-sm font-semibold',
          filtre === item.value
            ? 'border-spiritual-700 bg-spiritual-700 text-white'
            : 'border-cream-300 bg-white text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200',
        ]"
      >
        {{ item.label }}
      </Link>
    </div>

    <Card padding="none" class="border-t-2 border-gold-500">
      <ul v-if="avis.data.length" class="divide-y divide-cream-200 dark:divide-gray-700">
        <li v-for="item in avis.data" :key="item.id" class="p-5 sm:p-6">
          <div class="flex flex-wrap items-center gap-3">
            <span
              :class="[
                'inline-flex rounded-full px-3 py-1 text-xs font-semibold',
                item.statut === 'publie'
                  ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200'
                  : item.statut === 'rejete'
                    ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-200'
                    : 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-200',
              ]"
            >
              {{ item.statut_label }}
            </span>
            <span class="text-xs text-gray-500 dark:text-gray-400">{{ item.created_at }}</span>
          </div>

          <p class="mt-4 whitespace-pre-line font-display text-lg leading-relaxed text-gray-900 dark:text-white">
            {{ item.message }}
          </p>

          <div class="mt-5 flex flex-wrap gap-2">
            <Button v-if="item.statut !== 'publie'" variant="gold" size="sm" @click="moderer(item, 'publie')">
              Publier
            </Button>
            <Button v-if="item.statut !== 'rejete'" variant="secondary" size="sm" @click="moderer(item, 'rejete')">
              Rejeter
            </Button>
            <Button v-if="item.statut !== 'en_attente'" variant="secondary" size="sm" @click="moderer(item, 'en_attente')">
              Remettre en attente
            </Button>
            <Button variant="secondary" size="sm" @click="supprimer(item)">Supprimer</Button>
          </div>
        </li>
      </ul>
      <p v-else class="p-6 text-sm text-gray-500 dark:text-gray-400">Aucun avis pour ce filtre.</p>

      <nav v-if="avis.last_page > 1" class="flex items-center justify-between gap-3 border-t border-cream-200 px-5 py-4 text-sm dark:border-gray-700" aria-label="Pagination">
        <Link v-if="avis.prev_page_url" :href="avis.prev_page_url" class="inline-flex min-h-11 items-center rounded-lg px-3 font-semibold text-spiritual-700 dark:text-spiritual-300">Précédent</Link>
        <span class="text-gray-500">Page {{ avis.current_page }} sur {{ avis.last_page }}</span>
        <Link v-if="avis.next_page_url" :href="avis.next_page_url" class="inline-flex min-h-11 items-center rounded-lg px-3 font-semibold text-spiritual-700 dark:text-spiritual-300">Suivant</Link>
      </nav>
    </Card>
  </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';

const props = defineProps({
  avis: { type: Object, required: true },
  statuts: { type: Array, default: () => [] },
  filtre: { type: String, default: '' },
});

const filtres = computed(() => [
  { value: '', label: 'Tous' },
  ...props.statuts.map((item) => ({ value: item.value, label: item.label })),
]);

function moderer(item, statut) {
  router.patch(route('admin.avis.update', item.id), { statut }, { preserveScroll: true });
}

function supprimer(item) {
  if (window.confirm('Supprimer définitivement cet avis ?')) {
    router.delete(route('admin.avis.destroy', item.id), { preserveScroll: true });
  }
}
</script>
