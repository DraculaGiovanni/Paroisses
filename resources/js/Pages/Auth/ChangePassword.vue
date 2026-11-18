<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.put(route('password.force.update'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Changer le mot de passe" />

        <div class="mb-6">
            <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                Choisissez votre mot de passe
            </h1>
            <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">
                Pour la sécurité de votre compte, remplacez le mot de passe temporaire
                remis par l’administrateur. Il doit contenir au moins 12 caractères.
            </p>
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="password" value="Nouveau mot de passe" />
                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full"
                    required
                    minlength="12"
                    autocomplete="new-password"
                    autofocus
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel for="password_confirmation" value="Confirmer le mot de passe" />
                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    required
                    minlength="12"
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div class="mt-6 flex flex-wrap items-center gap-4">
                <PrimaryButton :disabled="form.processing">
                    Enregistrer mon mot de passe
                </PrimaryButton>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="min-h-11 rounded-md text-sm text-gray-600 underline hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                >
                    Se déconnecter
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
