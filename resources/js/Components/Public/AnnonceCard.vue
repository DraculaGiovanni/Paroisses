<template>
  <component
    :is="href ? Link : 'div'"
    :href="href"
    :class="['block h-full', href ? 'group rounded-3xl focus:outline-none focus-visible:ring-2 focus-visible:ring-spiritual-600' : '']"
  >
    <Card
      padding="none"
      hover
      :class="[
        'h-full',
        annonce.epinglee
          ? 'border-gold-300 bg-gold-50/40 dark:border-gold-800 dark:bg-gold-900/10'
          : '',
      ]"
    >
      <div class="flex h-full flex-col p-5 sm:p-6">
        <img
          v-if="annonce.image_url"
          :src="annonce.image_url"
          :alt="annonce.titre"
          class="mb-4 h-40 w-full rounded-2xl object-cover"
          loading="lazy"
        />

        <div class="flex flex-wrap items-center gap-2">
          <Badge :variant="annonce.epinglee ? 'warning' : 'default'" size="sm">
            {{ annonce.epinglee ? 'À la une' : annonce.categorie_label }}
          </Badge>
          <span v-if="annonce.categorie_label && annonce.epinglee" class="text-xs text-gray-500 dark:text-gray-400">
            {{ annonce.categorie_label }}
          </span>
        </div>

        <h3 class="mt-4 font-display text-xl leading-snug text-gray-900 dark:text-white sm:text-2xl">
          {{ annonce.titre }}
        </h3>

        <p class="mt-3 line-clamp-3 whitespace-pre-line text-sm leading-relaxed text-gray-600 dark:text-gray-300">
          {{ annonce.contenu }}
        </p>

        <div class="mt-auto flex items-center justify-between gap-3 pt-5 text-xs text-gray-500 dark:text-gray-400">
          <span>{{ formatDate(annonce.publie_le) }}</span>
          <span v-if="href" class="inline-flex items-center gap-1 font-semibold text-spiritual-700 transition-all group-hover:gap-2 dark:text-spiritual-300">
            Lire
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7-7 7M21 12H3" />
            </svg>
          </span>
        </div>
      </div>
    </Card>
  </component>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import Card from '@/Components/UI/Card.vue';
import Badge from '@/Components/UI/Badge.vue';

defineProps({
  annonce: {
    type: Object,
    required: true,
  },
  href: {
    type: String,
    default: null,
  },
});

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  if (Number.isNaN(date.getTime())) return dateString;
  return date.toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  });
};
</script>
