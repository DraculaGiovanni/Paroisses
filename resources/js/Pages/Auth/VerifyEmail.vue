<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>
        <Head title="Vérification de l'adresse e-mail" />

        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gold-700 dark:text-gold-300">
            Vérification
        </p>
        <h1 class="mt-3 font-display text-3xl text-gray-900 dark:text-white sm:text-4xl">
            Vérifiez votre adresse e-mail
        </h1>
        <p class="mt-3 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
            Un lien de vérification vous a été envoyé. Cliquez dessus pour activer votre compte.
            Si vous ne l'avez pas reçu, vous pouvez en demander un autre.
        </p>

        <div v-if="verificationLinkSent" class="mt-6 rounded-xl bg-green-50 px-4 py-3 text-sm font-medium text-green-800 dark:bg-green-900/20 dark:text-green-200">
            Un nouveau lien a été envoyé à votre adresse e-mail.
        </div>

        <form class="mt-8 flex flex-wrap items-center gap-4" @submit.prevent="submit">
            <PrimaryButton :class="{ 'opacity-50': form.processing }" :disabled="form.processing">
                Renvoyer le lien
            </PrimaryButton>

            <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="min-h-11 text-sm font-semibold text-gray-600 underline-offset-4 hover:underline dark:text-gray-400"
            >
                Se déconnecter
            </Link>
        </form>
    </GuestLayout>
</template>
