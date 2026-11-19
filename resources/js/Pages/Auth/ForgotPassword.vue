<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Mot de passe oublié" />

        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gold-700 dark:text-gold-300">
            Récupération
        </p>
        <h1 class="mt-3 font-display text-3xl text-gray-900 dark:text-white sm:text-4xl">
            Mot de passe oublié ?
        </h1>
        <p class="mt-3 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
            Indiquez votre adresse e-mail : nous vous enverrons un lien pour choisir un nouveau mot de passe.
        </p>

        <div v-if="status" class="mt-6 rounded-xl bg-green-50 px-4 py-3 text-sm font-medium text-green-800 dark:bg-green-900/20 dark:text-green-200">
            {{ status }}
        </div>

        <form class="mt-8 space-y-5" @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Adresse e-mail" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-2"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3">
                <Link
                    :href="route('login')"
                    class="text-sm font-semibold text-spiritual-700 underline-offset-4 hover:underline dark:text-spiritual-300"
                >
                    Retour à la connexion
                </Link>
                <PrimaryButton
                    class="w-full sm:w-auto"
                    :class="{ 'opacity-50': form.processing }"
                    :disabled="form.processing"
                >
                    Envoyer le lien
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
