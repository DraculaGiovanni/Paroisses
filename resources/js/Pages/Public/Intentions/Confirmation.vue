<template>
  <Head :title="`Demande envoyée — ${paroisse.nom}`" />

  <main class="min-h-screen bg-cream-50 px-4 py-10 dark:bg-gray-950 sm:px-6 sm:py-14" :style="{ '--accent': paroisse.couleur_principale || '#1E40AF' }">
    <div class="mx-auto grid max-w-6xl gap-8 lg:grid-cols-[16rem_1fr] lg:gap-12">
      <IntentionsNav :paroisse="paroisse" active="confirmation" />

      <div class="min-w-0 lg:pt-12">
        <div class="mx-auto max-w-xl">
          <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gold-100 text-gold-700 dark:bg-gold-900/30 dark:text-gold-300">
            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </div>

          <p class="mt-6 text-center text-xs font-semibold uppercase tracking-[0.18em] text-spiritual-700 dark:text-spiritual-300">
            Demande reçue
          </p>
          <h1 class="mt-3 text-center font-display text-4xl text-gray-900 dark:text-white sm:text-5xl">
            Votre intention est entre de bonnes mains
          </h1>
          <p class="mt-4 text-center leading-relaxed text-gray-600 dark:text-gray-400">
            La paroisse {{ paroisse.nom }} a reçu votre demande et vous en remercie.
          </p>

          <section class="mt-8 rounded-3xl border border-cream-300 bg-white p-6 shadow-soft dark:border-gray-700 dark:bg-gray-900 sm:p-8">
            <p class="text-center text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
              Votre code de suivi
            </p>
            <p class="mt-4 select-all break-all text-center font-mono text-3xl font-bold tracking-[0.25em] text-spiritual-800 dark:text-spiritual-200 sm:text-4xl">
              {{ code }}
            </p>

            <div class="mt-6 flex justify-center">
              <Button
                variant="secondary"
                aria-live="polite"
                @click="copyCode"
              >
                {{ copyLabel }}
              </Button>
            </div>

            <p class="mt-5 rounded-xl bg-amber-50 px-4 py-3 text-sm leading-relaxed text-amber-900 dark:bg-amber-900/20 dark:text-amber-200">
              Notez ce code ou faites une capture d'écran maintenant.
              Pour votre sécurité, il ne sera plus affiché après avoir quitté cette page.
            </p>
          </section>

          <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-center">
            <Button
              :href="route('paroisse.intentions.suivi', paroisse.slug)"
              variant="gold"
              size="lg"
            >
              Suivre ma demande
            </Button>
            <Button
              :href="route('paroisse.show', paroisse.slug)"
              variant="ghost"
              size="lg"
            >
              Retour à la paroisse
            </Button>
          </div>

          <p class="mt-6 text-center text-sm text-gray-500 dark:text-gray-400">
            Le code de suivi est personnel : il permet de consulter l'état de la demande
            sans afficher votre nom, votre téléphone ni le texte de votre intention.
          </p>
        </div>
      </div>
    </div>
  </main>
</template>

<script setup>
import { computed, onUnmounted, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import Button from '@/Components/UI/Button.vue';
import IntentionsNav from '@/Components/Public/IntentionsNav.vue';

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
