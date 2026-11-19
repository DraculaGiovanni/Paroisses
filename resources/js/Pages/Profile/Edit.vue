<script setup>
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const roleLabel = computed(() => ({
    super_admin: 'Super administrateur',
    admin: 'Administrateur',
    secretariat: 'Secrétariat',
}[user.role] || user.role));

const initiale = computed(() => (user.name || '?').charAt(0).toUpperCase());
</script>

<template>
    <Head title="Mon profil" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-display text-2xl text-gray-900 dark:text-white">
                Mon profil
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-6xl space-y-6 sm:px-6 lg:px-8">
                <!-- Bannière : identité visible immédiatement -->
                <section class="relative overflow-hidden rounded-3xl bg-spiritual-800 px-5 py-7 text-white sm:px-8 sm:py-9">
                    <div class="flex flex-wrap items-center gap-5">
                        <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-white/10 font-display text-2xl">
                            {{ initiale }}
                        </span>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gold-300">
                                {{ roleLabel }}
                            </p>
                            <h1 class="mt-2 truncate font-display text-3xl sm:text-4xl">
                                {{ user.name }}
                            </h1>
                            <p class="mt-1 break-all text-sm text-spiritual-100">
                                {{ user.email }}
                            </p>
                        </div>
                    </div>
                </section>

                <div class="grid gap-6 lg:grid-cols-2">
                    <Card padding="none" class="border-t-2 border-spiritual-600">
                        <div class="p-5 sm:p-7">
                            <UpdateProfileInformationForm
                                :must-verify-email="mustVerifyEmail"
                                :status="status"
                            />
                        </div>
                    </Card>

                    <div class="space-y-6">
                        <Card padding="none" class="border-t-2 border-gold-500">
                            <div class="p-5 sm:p-7">
                                <UpdatePasswordForm />
                            </div>
                        </Card>

                        <Card padding="none" class="border-t-2 border-red-600">
                            <div class="p-5 sm:p-7">
                                <DeleteUserForm />
                            </div>
                        </Card>
                    </div>
                </div>

                <p class="text-center text-xs text-gray-500 dark:text-gray-400">
                    Vos données restent lues uniquement par l'équipe de votre paroisse.
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
