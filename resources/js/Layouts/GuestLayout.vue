<script setup>
import { Link } from '@inertiajs/vue3';
import IconChurch from '@/Components/Icons/IconChurch.vue';
</script>

<template>
  <div class="min-h-screen bg-cream-50 dark:bg-gray-950 lg:grid lg:min-h-screen lg:grid-cols-2">
    <!-- Panneau éditorial : une seule idée visuelle, pas de photo générique -->
    <aside class="relative hidden overflow-hidden bg-spiritual-800 px-12 py-14 text-white lg:flex lg:flex-col lg:justify-between">
      <div class="pointer-events-none absolute -bottom-24 -right-20 h-96 w-96 text-white/5" aria-hidden="true">
        <IconChurch class="h-full w-full" />
      </div>

      <Link href="/" class="relative inline-flex items-center gap-3">
        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-white/10">
          <IconChurch class="h-5 w-5" />
        </span>
        <span class="font-display text-xl">Paroisses</span>
      </Link>

      <div class="relative max-w-lg">
        <p class="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-gold-300">
          <span class="inline-block h-2 w-2 rounded-full bg-gold-400" aria-hidden="true" />
          Ensemble, chaque jour
        </p>
        <p class="mt-6 font-display text-4xl leading-tight sm:text-5xl">
          Un lieu de prière,<br />un lieu de rencontre.
        </p>
        <p class="mt-6 text-sm leading-relaxed text-spiritual-100">
          Retrouvez les horaires de messe, les annonces de votre paroisse et suivez vos demandes d'intention.
        </p>
      </div>

      <p class="relative text-xs text-spiritual-200">
        Interface en français · Pensée pour les téléphones modestes
      </p>
    </aside>

    <main class="flex min-h-screen flex-col justify-center px-4 py-10 sm:px-6 lg:px-12">
      <div class="mx-auto w-full max-w-md">
        <Link href="/" class="mb-8 inline-flex items-center gap-2 text-spiritual-700 lg:hidden dark:text-spiritual-300">
          <span class="flex h-9 w-9 items-center justify-center rounded-full bg-spiritual-700 text-white">
            <IconChurch class="h-4 w-4" />
          </span>
          <span class="font-display text-lg">Paroisses</span>
        </Link>

        <div class="rounded-3xl border border-cream-300 bg-white p-6 shadow-soft-lg dark:border-gray-700 dark:bg-gray-900 sm:p-10">
          <slot />
        </div>
      </div>
    </main>
  </div>
</template>
