<template>
  <aside class="lg:sticky lg:top-8 lg:self-start">
    <!-- Navigation compacte sur téléphone -->
    <nav class="flex flex-wrap gap-2 lg:hidden" aria-label="Navigation demande de messe">
      <Link
        v-for="item in items"
        :key="item.href"
        :href="item.href"
        :class="[
          'inline-flex min-h-11 items-center rounded-full border px-4 text-sm font-semibold',
          item.active
            ? 'border-spiritual-700 bg-spiritual-700 text-white'
            : 'border-cream-300 bg-white text-spiritual-700 dark:border-gray-700 dark:bg-gray-900 dark:text-spiritual-300',
        ]"
      >
        {{ item.label }}
      </Link>
    </nav>

    <!-- Colonne éditoriale sur grand écran -->
    <div class="hidden lg:block">
      <p class="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-spiritual-700 dark:text-spiritual-300">
        <span class="inline-block h-2 w-2 rounded-full bg-gold-500" aria-hidden="true" />
        Demande de messe
      </p>
      <h2 class="mt-4 font-display text-3xl leading-tight text-gray-900 dark:text-white">
        Une intention,<br />une messe.
      </h2>
      <p class="mt-4 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
        La paroisse reçoit votre demande avec respect. Un code personnel vous permet
        de suivre son avancement, sans exposer vos informations.
      </p>

      <nav class="mt-8 space-y-1 border-l border-cream-300 dark:border-gray-700" aria-label="Navigation demande de messe">
        <Link
          v-for="item in items"
          :key="item.href"
          :href="item.href"
          :class="[
            'flex min-h-11 items-center border-l-2 pl-4 text-sm font-medium transition-colors',
            item.active
              ? 'border-gold-500 text-spiritual-800 dark:text-spiritual-200'
              : '-ml-px border-transparent text-gray-600 hover:text-spiritual-700 dark:text-gray-400 dark:hover:text-spiritual-300',
          ]"
        >
          {{ item.label }}
        </Link>
      </nav>

      <div class="mt-10 rounded-2xl border border-cream-300 bg-white p-5 dark:border-gray-700 dark:bg-gray-900">
        <p class="font-display text-lg text-gray-900 dark:text-white">Confidentialité</p>
        <p class="mt-2 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
          Vos coordonnées ne sont jamais affichées publiquement et sont supprimées
          90 jours après la célébration.
        </p>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  paroisse: { type: Object, required: true },
  active: {
    type: String,
    default: 'nouvelle',
    validator: (value) => ['nouvelle', 'suivi', 'confirmation'].includes(value),
  },
});

const items = computed(() => [
  {
    label: 'Demander une messe',
    href: route('paroisse.intentions.nouvelle', props.paroisse.slug),
    active: props.active === 'nouvelle',
  },
  {
    label: 'Suivre ma demande',
    href: route('paroisse.intentions.suivi', props.paroisse.slug),
    active: props.active === 'suivi',
  },
  {
    label: 'Retour à la paroisse',
    href: route('paroisse.show', props.paroisse.slug),
    active: false,
  },
]);
</script>
