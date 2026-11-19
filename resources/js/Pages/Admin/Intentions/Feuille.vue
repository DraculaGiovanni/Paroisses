<template>
    <Head :title="`Feuille des intentions — ${formatDate(date)}`" />

    <main class="min-h-screen bg-cream-50 px-4 py-8 text-gray-900 dark:bg-gray-950 dark:text-white print:bg-white print:px-0 print:py-0 print:text-black">
        <div class="mx-auto max-w-4xl">
            <!-- Commandes : absentes du document imprimé -->
            <div class="print:hidden">
                <Link
                    :href="route('admin.intentions.index')"
                    class="inline-flex min-h-11 items-center gap-2 text-sm font-semibold text-spiritual-700 dark:text-spiritual-300"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Retour aux intentions
                </Link>

                <div class="mt-5 flex flex-wrap items-end justify-between gap-5">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gold-700 dark:text-gold-300">
                            Document du célébrant
                        </p>
                        <h1 class="mt-2 font-display text-3xl text-gray-900 dark:text-white sm:text-4xl">
                            Feuille des intentions
                        </h1>
                    </div>

                    <Button variant="gold" size="lg" type="button" @click="imprimer">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2m-12-4h12v8H6v-8z" />
                        </svg>
                        Imprimer
                    </Button>
                </div>

                <div class="mt-7 flex flex-wrap items-center gap-3 rounded-2xl border border-cream-300 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                    <button
                        type="button"
                        class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-cream-300 text-gray-600 hover:bg-cream-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                        :aria-label="`Jour précédent : ${formatDate(previousDate)}`"
                        @click="charger(previousDate)"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m7 7l-7-7 7-7" />
                        </svg>
                    </button>

                    <div>
                        <label for="date_feuille" class="block text-xs font-medium text-gray-500 dark:text-gray-400">
                            Date de la messe
                        </label>
                        <input
                            id="date_feuille"
                            v-model="date"
                            type="date"
                            class="mt-1 block min-h-11 rounded-xl border-0 bg-cream-50 px-3 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-cream-300 focus:ring-2 focus:ring-spiritual-600 dark:bg-gray-800 dark:text-white dark:ring-gray-700"
                        />
                    </div>

                    <Button type="button" variant="secondary" @click="charger(date)">Afficher</Button>

                    <button
                        type="button"
                        class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-cream-300 text-gray-600 hover:bg-cream-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                        :aria-label="`Jour suivant : ${formatDate(nextDate)}`"
                        @click="charger(nextDate)"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7-7l7 7-7 7" />
                        </svg>
                    </button>

                    <p class="ml-auto text-sm font-display text-gray-700 dark:text-gray-300">
                        {{ formatDate(date) }}
                    </p>
                </div>
            </div>

            <!-- Document imprimable -->
            <article class="mt-8 border-t-4 border-gold-500 bg-white p-5 text-gray-900 shadow-soft dark:bg-gray-900 dark:text-white print:mt-0 print:border-black print:p-0 print:shadow-none sm:p-8">
                <header class="flex flex-wrap items-end justify-between gap-4 border-b border-cream-300 pb-5 dark:border-gray-700 print:border-black">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-spiritual-700 dark:text-spiritual-300 print:text-black">
                            {{ paroisse?.nom || 'Paroisse' }}
                        </p>
                        <h2 class="mt-2 font-display text-3xl print:text-2xl">
                            Intentions de messe
                        </h2>
                    </div>
                    <p class="font-display text-xl print:text-base">
                        {{ formatDate(date) }}
                    </p>
                </header>

                <p class="mt-4 text-xs leading-relaxed text-gray-600 dark:text-gray-400 print:text-black">
                    Document interne à remettre au célébrant. Les coordonnées personnelles ne figurent pas sur cette feuille.
                </p>

                <div v-if="groupes.length > 0" class="mt-8 space-y-8 print:space-y-6">
                    <section v-for="groupe in groupes" :key="groupe.horaire" class="break-inside-avoid">
                        <div class="flex flex-wrap items-center gap-3 border-b border-cream-300 pb-2 dark:border-gray-700 print:border-black">
                            <span class="inline-flex min-h-9 items-center rounded-lg bg-spiritual-700 px-3 text-sm font-semibold text-white print:bg-black">
                                {{ groupe.heure || groupe.horaire }}
                            </span>
                            <h3 class="font-display text-xl">
                                {{ groupe.horaire }}
                            </h3>
                            <span v-if="groupe.lieu" class="text-sm text-gray-600 dark:text-gray-400 print:text-black">
                                {{ groupe.lieu }}
                            </span>
                            <span class="ml-auto text-xs text-gray-500 dark:text-gray-400 print:text-black">
                                {{ groupe.intentions.length }} intention(s)
                            </span>
                        </div>

                        <ol class="mt-4 space-y-5">
                            <li
                                v-for="(intention, index) in groupe.intentions"
                                :key="intention.id"
                                class="break-inside-avoid"
                            >
                                <div class="flex flex-wrap items-baseline justify-between gap-2">
                                    <p class="font-semibold">
                                        {{ index + 1 }}. {{ intention.nom_demandeur }}
                                    </p>
                                    <span class="font-mono text-xs text-gray-500 print:text-black">
                                        {{ intention.code_suivi }}
                                    </span>
                                </div>
                                <p class="mt-1 text-xs uppercase tracking-wide text-gold-700 dark:text-gold-300 print:text-black">
                                    {{ intention.type_label }}
                                </p>
                                <p class="mt-2 whitespace-pre-line leading-relaxed">
                                    {{ intention.intention }}
                                </p>
                            </li>
                        </ol>
                    </section>
                </div>

                <p v-else class="mt-10 text-gray-600 dark:text-gray-400 print:text-black">
                    Aucune intention programmée pour cette date.
                </p>

                <footer class="mt-12 hidden border-t border-cream-300 pt-4 text-xs text-gray-500 print:block print:border-black print:text-black">
                    Feuille des intentions · {{ paroisse?.nom || 'Paroisse' }} · {{ formatDate(date) }}
                </footer>
            </article>
        </div>
    </main>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
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
    if (!value) return '';
    const [year, month, day] = value.split('-').map(Number);
    if (!year || !month || !day || !MOIS[month - 1]) return value;
    return `${day} ${MOIS[month - 1]} ${year}`;
}

function decaler(jours) {
    const base = new Date(`${props.date}T00:00:00Z`);
    if (Number.isNaN(base.getTime())) return props.date;
    base.setUTCDate(base.getUTCDate() + jours);
    return base.toISOString().slice(0, 10);
}

const previousDate = computed(() => decaler(-1));
const nextDate = computed(() => decaler(1));

function charger(valeur) {
    if (!valeur) return;
    date.value = valeur;
    router.get(route('admin.intentions.feuille'), { date: valeur }, {
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
        size: A4;
        margin: 1.5cm;
    }

    body {
        background: #fff !important;
    }
}
</style>
