<template>
    <Head :title="`Suivi d'une demande — ${paroisse.nom}`" />

    <main class="min-h-screen bg-cream-50 px-4 py-8 dark:bg-gray-950 sm:py-12">
        <div class="mx-auto max-w-xl">
            <Link
                :href="route('paroisse.show', paroisse.slug)"
                class="inline-flex min-h-11 items-center gap-2 text-sm font-semibold text-spiritual-700 dark:text-spiritual-300"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                {{ paroisse.nom }}
            </Link>

            <header class="mt-6">
                <p class="text-xs font-semibold uppercase tracking-wide text-spiritual-700 dark:text-spiritual-300">
                    Demande de messe
                </p>
                <h1 class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                    Suivre ma demande
                </h1>
                <p class="mt-3 text-gray-600 dark:text-gray-400">
                    Saisissez le code de suivi remis lors de l'envoi de votre demande.
                </p>
            </header>

            <div
                v-if="form.errors.rate_limit"
                role="alert"
                class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-200"
            >
                {{ form.errors.rate_limit }}
            </div>

            <form
                class="mt-6 space-y-5 rounded-2xl border border-cream-300 bg-white p-5 shadow-soft dark:border-gray-700 dark:bg-gray-900 sm:p-6"
                @submit.prevent="submit"
            >
                <div>
                    <label for="code_suivi" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Code de suivi <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="code_suivi"
                        v-model="form.code_suivi"
                        type="text"
                        name="code_suivi"
                        maxlength="10"
                        autocomplete="off"
                        autocapitalize="characters"
                        spellcheck="false"
                        placeholder="ABCDEFGHJK"
                        :class="[
                            'block w-full rounded-xl border-0 px-4 py-3 font-mono text-lg uppercase tracking-[0.25em] text-gray-900 shadow-sm ring-1 ring-inset transition-all duration-200 placeholder:font-sans placeholder:text-base placeholder:normal-case placeholder:tracking-normal placeholder:text-gray-400 focus:ring-2 focus:ring-inset sm:text-xl dark:bg-gray-900 dark:text-white',
                            form.errors.code_suivi
                                ? 'ring-red-300 focus:ring-red-500'
                                : 'ring-cream-300 focus:ring-spiritual-600 dark:ring-gray-700',
                        ]"
                    />
                    <p v-if="form.errors.code_suivi" class="mt-1.5 text-sm text-red-600 dark:text-red-400">
                        {{ form.errors.code_suivi }}
                    </p>
                    <p v-else class="mt-1.5 text-sm text-gray-500 dark:text-gray-400">
                        10 caractères, lettres et chiffres. Les majuscules et minuscules sont acceptées.
                    </p>
                </div>

                <div class="absolute left-[-9999px] top-auto h-px w-px overflow-hidden" aria-hidden="true">
                    <label for="reference_colis">Ne pas remplir ce champ</label>
                    <input
                        id="reference_colis"
                        v-model="form.reference_colis"
                        type="text"
                        name="reference_colis"
                        tabindex="-1"
                        autocomplete="off"
                    />
                </div>

                <input v-model="form.form_token" type="hidden" name="form_token" />

                <Button
                    type="submit"
                    size="lg"
                    full-width
                    :loading="form.processing"
                    :disabled="form.processing"
                >
                    Consulter ma demande
                </Button>
            </form>

            <section
                v-if="resultat"
                aria-live="polite"
                class="mt-6 rounded-2xl border border-cream-300 bg-white p-5 shadow-soft dark:border-gray-700 dark:bg-gray-900 sm:p-6"
            >
                <div class="flex flex-wrap items-center gap-3">
                    <span :class="['inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold', statut.classes]">
                        {{ statut.label }}
                    </span>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        État de votre demande
                    </h2>
                </div>

                <p class="mt-3 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                    {{ statut.description }}
                </p>

                <dl v-if="hasSchedule || resultat.motif_refus" class="mt-5 space-y-3 border-t border-cream-200 pt-4 text-sm dark:border-gray-700">
                    <div v-if="resultat.date_programmee" class="flex flex-col gap-1 sm:flex-row sm:gap-2">
                        <dt class="font-semibold text-gray-900 dark:text-white">Date programmée :</dt>
                        <dd class="text-gray-600 dark:text-gray-300">{{ formatDate(resultat.date_programmee) }}</dd>
                    </div>
                    <div v-if="resultat.heure_programmee" class="flex flex-col gap-1 sm:flex-row sm:gap-2">
                        <dt class="font-semibold text-gray-900 dark:text-white">Heure :</dt>
                        <dd class="text-gray-600 dark:text-gray-300">{{ resultat.heure_programmee }}</dd>
                    </div>
                    <div v-if="resultat.motif_refus" class="flex flex-col gap-1 sm:flex-row sm:gap-2">
                        <dt class="font-semibold text-gray-900 dark:text-white">Motif du refus :</dt>
                        <dd class="text-gray-600 dark:text-gray-300">{{ resultat.motif_refus }}</dd>
                    </div>
                </dl>

                <p class="mt-5 rounded-xl bg-cream-100 px-4 py-3 text-xs leading-relaxed text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                    Pour protéger votre vie privée, cet écran n'affiche ni votre nom, ni votre téléphone,
                    ni le texte de votre intention.
                </p>
            </section>

            <div
                v-else-if="message"
                role="status"
                class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-900 dark:border-amber-900/50 dark:bg-amber-900/20 dark:text-amber-200"
            >
                {{ message }}
            </div>

            <p class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
                Pas encore de demande ?
                <Link
                    :href="route('paroisse.intentions.nouvelle', paroisse.slug)"
                    class="font-semibold text-spiritual-700 underline-offset-4 hover:underline dark:text-spiritual-300"
                >
                    Demander une messe
                </Link>
            </p>
        </div>
    </main>
</template>

<script setup>
import { computed, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Button from '@/Components/UI/Button.vue';

const props = defineProps({
    paroisse: { type: Object, required: true },
    form_token: { type: String, required: true },
    resultat: { type: Object, default: null },
    message: { type: String, default: null },
});

const STATUTS = {
    recue: {
        label: 'Reçue',
        classes: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-200',
        description: 'Votre demande a bien été reçue par la paroisse. Elle sera examinée par l\'équipe.',
    },
    confirmee: {
        label: 'Confirmée',
        classes: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-200',
        description: 'La paroisse a confirmé votre demande. La date et l\'heure vous seront communiquées prochainement.',
    },
    programmee: {
        label: 'Programmée',
        classes: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200',
        description: 'Votre intention est inscrite à une messe. Merci de votre confiance.',
    },
    celebree: {
        label: 'Célébrée',
        classes: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200',
        description: 'Votre intention a été célébrée. La paroisse vous remercie de votre confiance.',
    },
    refusee: {
        label: 'Refusée',
        classes: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-200',
        description: 'La paroisse n\'a pas pu retenir cette demande. Le motif est indiqué ci-dessous lorsqu\'il est disponible.',
    },
    annulee: {
        label: 'Annulée',
        classes: 'bg-amber-100 text-amber-900 dark:bg-amber-900/30 dark:text-amber-200',
        description: 'Cette demande a été annulée. Vous pouvez envoyer une nouvelle demande si besoin.',
    },
};

const MOIS = [
    'janvier', 'février', 'mars', 'avril', 'mai', 'juin',
    'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre',
];

const form = useForm({
    code_suivi: '',
    reference_colis: '',
    form_token: props.form_token,
});

watch(
    () => props.form_token,
    (token) => {
        form.form_token = token;
    },
);

const statut = computed(() => STATUTS[props.resultat?.statut] ?? {
    label: 'Statut inconnu',
    classes: 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
    description: 'Le statut de cette demande n\'est pas disponible pour le moment.',
});

const hasSchedule = computed(() =>
    Boolean(props.resultat?.date_programmee || props.resultat?.heure_programmee),
);

function formatDate(value) {
    const [year, month, day] = value.split('-').map(Number);

    if (!year || !month || !day || !MOIS[month - 1]) {
        return value;
    }

    return `${day} ${MOIS[month - 1]} ${year}`;
}

function submit() {
    form.post(route('paroisse.intentions.suivi.rechercher', props.paroisse.slug), {
        preserveScroll: true,
    });
}
</script>
