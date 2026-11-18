<template>
  <Card 
    :class="{ 'border-gold-400 dark:border-gold-600 bg-gold-50/30 dark:bg-gold-900/10': annonce.epinglee }"
    hover
  >
    <div class="p-6">
      <div class="flex items-start justify-between gap-4 mb-3">
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white flex-1">
          {{ annonce.titre }}
        </h3>
        
        <Badge v-if="annonce.epinglee" variant="warning" size="sm">
          <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
          </svg>
          Épinglée
        </Badge>
      </div>

      <Badge v-if="annonce.categorie_label" variant="secondary" size="sm" class="mb-3">
        {{ annonce.categorie_label }}
      </Badge>
      
      <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line line-clamp-3 mb-4">
        {{ annonce.contenu }}
      </p>
      
      <div class="flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
        <span class="flex items-center gap-1">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          {{ formatDate(annonce.publie_le) }}
        </span>
        
        <span v-if="annonce.date_debut && annonce.date_fin" class="flex items-center gap-1">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Du {{ formatDate(annonce.date_debut) }} au {{ formatDate(annonce.date_fin) }}
        </span>
      </div>
    </div>
  </Card>
</template>

<script setup>
import Card from '@/Components/UI/Card.vue';
import Badge from '@/Components/UI/Badge.vue';

defineProps({
  annonce: {
    type: Object,
    required: true,
  },
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
</script>
