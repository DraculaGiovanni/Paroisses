<template>
    <Head :title="`Demande envoyée — ${paroisse.nom}`" />

    <main class="min-h-screen bg-cream-50 px-4 py-10 dark:bg-gray-950 sm:py-14">
        <div class="mx-auto max-w-xl text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <h1 class="mt-6 text-3xl font-bold text-gray-900 dark:text-white">
                Votre demande a bien été envoyée
            </h1>
            <p class="mt-3 text-gray-600 dark:text-gray-400">
                La paroisse {{ paroisse.nom }} a reçu votre intention et vous en remercie.
            </p>

            <section class="mt-8 rounded-2xl border border-cream-300 bg-white p-5 shadow-soft dark:border-gray-700 dark:bg-gray-900 sm:p-6">
                <p class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    Votre code de suivi
                </p>
                <p class="mt-3 select-all break-all font-mono text-3xl font-bold tracking-[0.25em] text-spiritual-800 dark:text-spiritual-200 sm:text-4xl">
                    {{ code }}
                </p>

                <Button
                    class="mt-5 min-h-11"
                    variant="secondary"
                    aria-live="polite"
                    @click="copyCode"
                >
                    {{ copyLabel }}
                </Button>

                <p class="mt-4 rounded-xl bg-amber-50 px-4 py-3 text-sm leading-relaxed text-amber-900 dark:bg-amber-900/20 dark:text-amber-200">
                    Notez ce code ou faites une capture d'écran maintenant.
                    Pour votre sécurité, il ne sera plus affiché après avoir quitté cette page.
                </p>
            </section>

            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-center">
                <Button
                    :href="route('paroisse.intentions.suivi', paroisse.slug)"
                    variant="primary"
                    size="lg"
                    class="min-h-11"
                >
                    Suivre ma demande
                </Button>
                <Button
                    :href="route('paroisse.show', paroisse.slug)"
                    variant="ghost"
                    size="lg"
                    class="min-h-11"
                >
                    Retour à la paroisse
                </Button>
            </div>

            <p class="mt-6 text-sm text-gray-500 dark:text-gray-400">
                Le code de suivi est personnel : il permet de consulter l'état de la demande
                sans afficher votre nom, votre téléphone ni le texte de votre intention.
            </p>
        </div>
    </main>
</template>

<script setup>
import { computed, onUnmounted, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import Button from '@/Components/UI/Button.vue';

const props = defineProps({
    paroisse: { type: Object, required: true },
    code: { type: String, required: true },
});

const copyState = ref('idle');
let resetTimeout = null;

const copyLabel = computed(() => {
    if (copyState.value === 'copied') {
        return 'Code copié';
    }

    if (copyState.value === 'failed') {
        return 'Copie impossible — sélectionnez le code';
    }

    return 'Copier le code';
});

async function copyCode() {
    let copied = false;

    try {
        if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
            await navigator.clipboard.writeText(props.code);
            copied = true;
        }
    } catch {
        copied = false;
    }

    if (!copied) {
        copied = copyWithTemporaryTextarea();
    }

    copyState.value = copied ? 'copied' : 'failed';

    if (resetTimeout !== null) {
        clearTimeout(resetTimeout);
    }

    resetTimeout = setTimeout(() => {
        copyState.value = 'idle';
    }, 2500);
}

function copyWithTemporaryTextarea() {
    const textarea = document.createElement('textarea');
    textarea.value = props.code;
    textarea.setAttribute('readonly', '');
    textarea.style.position = 'absolute';
    textarea.style.left = '-9999px';
    document.body.appendChild(textarea);
    textarea.select();

    let copied = false;

    try {
        copied = document.execCommand('copy');
    } catch {
        copied = false;
    }

    document.body.removeChild(textarea);

    return copied;
}

onUnmounted(() => {
    if (resetTimeout !== null) {
        clearTimeout(resetTimeout);
    }
});
</script>
