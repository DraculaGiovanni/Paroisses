<template>
    <Head :title="`Feuille des intentions — ${formatDate(date)}`" />

    <main class="min-h-screen bg-white px-4 py-8 text-gray-900 print:px-0 print:py-0">
        <div class="mx-auto max-w-3xl">
            <div class="print:hidden">
                <Link
                    :href="route('admin.intentions.index')"
                    class="inline-flex min-h-11 items-center gap-2 text-sm font-semibold text-spiritual-700"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Retour aux intentions
                </Link>

                <h1 class="mt-4 text-2xl font-bold">Feuille des intentions</h1>

                <form class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end" @submit.prevent="charger">
                    <div class="w-full sm:max-w-xs">
                        <label for="date_feuille" class="mb-1.5 block text-sm font-medium text-gray-700">
                            Date de la messe
                        </label>
                        <input
                            id="date_feuille"
                            v-model="date"
                            type="date"
                            class="block w-full rounded-xl border-0 px-4 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-cream-300 focus:ring-2 focus:ring-inset focus:ring-spiritual-600 sm:text-sm"
                        />
                    </div>
                    <Button type="submit" variant="secondary">Afficher</Button>
                    <Button type="button" variant="primary" @click="imprimer">Imprimer</Button>
                </form>
            </div>

            <header class="mt-8 border-b border-gray-300 pb-4 print:mt-0">
                <p class="text-sm font-semibold uppercase tracking-wide text-gray-600">
                    {{ paroisse?.nom || 'Paroisse' }}
                </p>
                <h2 class="mt-1 text-2xl font-bold">Intentions de messe</h2>
                <p class="mt-1 text-gray-700">
                    {{ formatDate(date) }}
                </p>
                <p class="mt-1 text-sm text-gray-500">
                    Document interne à remettre au célébrant.
                </p>
            </header>

            <div v-if="groupes.length > 0" class="mt-6 space-y-8">
                <section
                    v-for="groupe in groupes"
                    :key="groupe.horaire"
                    class="break-inside-avoid"
                >
                    <div class="flex flex-wrap items-baseline justify-between gap-2 border-b border-gray-300 pb-2">
                        <h3 class="text-lg font-bold">{{ groupe.horaire }}</h3>
                        <span class="text-sm text-gray-600">
                            {{ groupe.intentions.length }} intention(s)
                        </span>
                    </div>

                    <ol class="mt-3 space-y-4">
                        <li
                            v-for="(intention, index) in groupe.intentions"
                            :key="intention.id"
                            class="break-inside-avoid"
                        >
                            <div class="flex items-baseline justify-between gap-3">
                                <p class="font-semibold">
                                    {{ index + 1 }}. {{ intention.nom_demandeur }}
                                </p>
                                <span class="font-mono text-xs text-gray-500">{{ intention.code_suivi }}</span>
                            </div>
                            <p class="text-sm text-gray-600">{{ intention.type_label }}</p>
                            <p class="mt-1 whitespace-pre-line">{{ intention.intention }}</p>
                        </li>
                    </ol>
                </section>
            </div>

            <p v-else class="mt-8 text-gray-600">
                Aucune intention programmée pour cette date.
            </p>
        </div>
    </main>
</template>

<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import Button from '@/Components/UI/Button.vue';

const props = defineProps({
    date: { type: String, required: true },
    groupes: { type: Array, default: () => [] },
});

const page = usePage();
const paroisse = page.props.paroisse;

const date = ref(props.date);

watch(
    () => props.date,
    (value) => {
        date.value = value;
    },
);

const MOIS = [
    'janvier', 'février', 'mars', 'avril', 'mai', 'juin',
    'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre',
];

function formatDate(value) {
    if (!value) {
        return '';
    }

    const [year, month, day] = value.split('-').map(Number);

    if (!year || !month || !day || !MOIS[month - 1]) {
        return value;
    }

    return `${day} ${MOIS[month - 1]} ${year}`;
}

function charger() {
    router.get(route('admin.intentions.feuille'), { date: date.value }, {
        preserveScroll: true,
        preserveState: true,
    });
}

function imprimer() {
    window.print();
}
</script>

<style>
@media print {
    @page {
        margin: 1.5cm;
    }
}
</style>
