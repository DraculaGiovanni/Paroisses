<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Confirmation du mot de passe" />

        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gold-700 dark:text-gold-300">
            Sécurité
        </p>
        <h1 class="mt-3 font-display text-3xl text-gray-900 dark:text-white sm:text-4xl">
            Confirmez votre mot de passe
        </h1>
        <p class="mt-3 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
            Cette zone est protégée. Saisissez votre mot de passe pour continuer.
        </p>

        <form class="mt-8 space-y-5" @submit.prevent="submit">
            <div>
                <InputLabel for="password" value="Mot de passe" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-2"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    autofocus
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <PrimaryButton
                class="w-full sm:w-auto"
                :class="{ 'opacity-50': form.processing }"
                :disabled="form.processing"
            >
                Confirmer
            </PrimaryButton>
        </form>
    </GuestLayout>
</template>
