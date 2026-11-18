<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    paroisse: { type: Object, required: true },
    annonce: { type: Object, required: true },
});
</script>

<template>
    <Head :title="`${annonce.titre} — ${paroisse.nom}`" />
    <main class="min-h-screen bg-cream-50 px-4 py-10 dark:bg-gray-950">
        <article class="mx-auto max-w-3xl rounded-xl border border-cream-300 bg-white p-6 dark:border-gray-700 dark:bg-gray-900">
            <Link :href="route('paroisse.annonces.index', paroisse.slug)" class="text-sm text-spiritual-700">
                ← Toutes les annonces
            </Link>
            <p class="mt-6 text-xs font-semibold text-spiritual-700">{{ annonce.categorie_label }}</p>
            <h1 class="mt-2 text-3xl font-bold">{{ annonce.titre }}</h1>
            <p class="mt-6 whitespace-pre-line text-gray-700 dark:text-gray-300">{{ annonce.contenu }}</p>
        </article>
    </main>
</template>
