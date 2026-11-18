<template>
  <div 
    class="relative overflow-hidden bg-gradient-to-br from-spiritual-900 to-spiritual-700 py-20 px-4 sm:px-6 lg:px-8"
    :style="{ '--accent-color': accentColor }"
  >
    <!-- Motif de fond -->
    <div class="absolute inset-0 opacity-10">
      <div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 40px 40px;"></div>
    </div>
    
    <div class="relative max-w-7xl mx-auto text-center">
      <h1 
        class="text-4xl sm:text-5xl lg:text-6xl font-bold text-white mb-4"
        v-motion
        :initial="{ opacity: 0, y: 20 }"
        :enter="{ opacity: 1, y: 0, transition: { duration: 600 } }"
      >
        {{ paroisse.nom }}
      </h1>
      
      <p 
        v-if="paroisse.diocese"
        class="text-xl text-spiritual-100 mb-8"
        v-motion
        :initial="{ opacity: 0, y: 20 }"
        :enter="{ opacity: 1, y: 0, transition: { duration: 600, delay: 200 } }"
      >
        Diocèse de {{ paroisse.diocese }}
      </p>
      
      <div 
        v-if="paroisse.adresse || paroisse.telephone"
        class="flex flex-col sm:flex-row items-center justify-center gap-4 text-spiritual-100"
        v-motion
        :initial="{ opacity: 0, y: 20 }"
        :enter="{ opacity: 1, y: 0, transition: { duration: 600, delay: 400 } }"
      >
        <span v-if="paroisse.adresse" class="flex items-center gap-2">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
          {{ paroisse.adresse }}
        </span>
        
        <span v-if="paroisse.telephone" class="flex items-center gap-2">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
          </svg>
          {{ paroisse.telephone }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  paroisse: {
    type: Object,
    required: true,
  },
  accentColor: {
    type: String,
    default: '#1E40AF',
  },
});
</script>
