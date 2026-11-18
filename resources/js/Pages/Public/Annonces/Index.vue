<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    paroisse: { type: Object, required: true },
    annonces: { type: Object, required: true },
    categorie: { type: String, default: null },
});
</script>

<template>
    <Head :title="`Annonces — ${paroisse.nom}`" />
    <main class="min-h-screen bg-cream-50 px-4 py-10 dark:bg-gray-950">
        <div class="mx-auto max-w-3xl">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Annonces</h1>
            <div v-if="categorie" class="mt-2 text-sm text-gray-600">Filtre : {{ categorie }}</div>

            <div v-if="annonces.data.length" class="mt-6 space-y-4">
                <Link
                    v-for="annonce in annonces.data"
                    :key="annonce.id"
                    :href="route('paroisse.annonces.show', [paroisse.slug, annonce.id])"
                    class="block rounded-xl border border-cream-300 bg-white p-5 dark:border-gray-700 dark:bg-gray-900"
                >
                    <span class="text-xs font-semibold text-spiritual-700">{{ annonce.categorie_label }}</span>
                    <h2 class="mt-1 text-lg font-semibold">{{ annonce.titre }}</h2>
                    <p class="mt-2 line-clamp-2 text-sm text-gray-600 dark:text-gray-400">{{ annonce.contenu }}</p>
                </Link>
            </div>
            <p v-else class="mt-8 text-gray-500">Aucune annonce publiée.</p>
        </div>
    </main>
</template>
