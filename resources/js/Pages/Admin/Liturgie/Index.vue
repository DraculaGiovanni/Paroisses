<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Card from '@/Components/UI/Card.vue';
import Input from '@/Components/UI/Input.vue';
import Select from '@/Components/UI/Select.vue';
import Button from '@/Components/UI/Button.vue';

const props = defineProps({
    couleurs: {
        type: Array,
        default: () => [],
    },
    couleursDisponibles: {
        type: Object,
        default: () => ({}),
    },
});

const form = useForm({
    date: '',
    couleur: 'rouge',
    libelle: '',
});

const colorOptions = Object.entries(props.couleursDisponibles).map(([value, label]) => ({
    value,
    label,
}));

const LEGENDE = {
    violet: '#7C3AED',
    rose: '#F472B6',
    blanc: '#FFFFFF',
    rouge: '#DC2626',
    vert: '#15803D',
    noir: '#1F2937',
};

const MOIS = [
    'janvier', 'février', 'mars', 'avril', 'mai', 'juin',
    'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre',
];

function formatDate(valeur) {
    if (!valeur) return '';
    const [year, month, day] = valeur.split('-').map(Number);
    if (!year || !month || !day || !MOIS[month - 1]) return valeur;
    return `${day} ${MOIS[month - 1]} ${year}`;
}

const submit = () => {
    form.post(route('admin.liturgie.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};

const remove = (couleur) => {
    router.delete(route('admin.liturgie.destroy', couleur.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Calendrier liturgique" />

    <AdminLayout>
        <PageHeader
            eyebrow="Calendrier liturgique"
            title="Couleurs exceptionnelles"
            description="Définissez la couleur d'une journée particulière pour votre paroisse."
        />

        <!-- Légende : la couleur est toujours accompagnée d'un libellé -->
        <Card padding="none" class="border-t-2 border-gold-500">
            <div class="border-b border-cream-200 px-5 py-4 dark:border-gray-700 sm:px-6">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gold-700 dark:text-gold-300">
                    Légende
                </p>
            </div>
            <div class="grid grid-cols-2 gap-3 p-5 sm:grid-cols-3 sm:p-6 lg:grid-cols-6">
                <div
                    v-for="(hex, nom) in LEGENDE"
                    :key="nom"
                    class="flex items-center gap-3 rounded-xl bg-cream-50 px-3 py-2.5 dark:bg-gray-800"
                >
                    <span
                        class="h-6 w-6 shrink-0 rounded-full border border-gray-300 dark:border-gray-600"
                        :style="{ backgroundColor: hex }"
                        aria-hidden="true"
                    />
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">
                        {{ couleursDisponibles[nom] || nom }}
                    </span>
                </div>
            </div>
        </Card>

        <div class="mt-6 grid gap-5 sm:gap-6 lg:grid-cols-2">
            <Card padding="none" class="border-t-2 border-spiritual-600">
                <div class="border-b border-cream-200 px-5 py-4 dark:border-gray-700 sm:px-6">
                    <h2 class="font-display text-xl text-gray-900 dark:text-white">
                        Ajouter une exception
                    </h2>
                </div>
                <form class="space-y-5 p-5 sm:p-6" @submit.prevent="submit">
                    <Input v-model="form.date" type="date" label="Date" :error="form.errors.date" required />
                    <Select v-model="form.couleur" label="Couleur" :options="colorOptions" :error="form.errors.couleur" required />
                    <Input v-model="form.libelle" label="Libellé (optionnel)" :error="form.errors.libelle" />
                    <Button type="submit" variant="gold" size="lg" :loading="form.processing">
                        Enregistrer
                    </Button>
                </form>
            </Card>

            <Card padding="none" class="border-t-2 border-spiritual-600">
                <div class="border-b border-cream-200 px-5 py-4 dark:border-gray-700 sm:px-6">
                    <h2 class="font-display text-xl text-gray-900 dark:text-white">
                        Dates enregistrées
                    </h2>
                </div>
                <div class="space-y-3 p-5 sm:p-6">
                    <p v-if="couleurs.length === 0" class="text-sm text-gray-500 dark:text-gray-400">
                        Aucune couleur exceptionnelle pour le moment.
                    </p>

                    <div
                        v-for="couleur in couleurs"
                        :key="couleur.id"
                        class="flex flex-wrap items-center gap-3 rounded-2xl border border-cream-300 bg-cream-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-800"
                    >
                        <span
                            class="h-9 w-9 shrink-0 rounded-full border border-gray-300 dark:border-gray-600"
                            :style="{ backgroundColor: LEGENDE[couleur.couleur] || '#FFFFFF' }"
                            aria-hidden="true"
                        />
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-gray-900 dark:text-white">
                                {{ formatDate(couleur.date) }}
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                {{ couleur.libelle || (couleursDisponibles[couleur.couleur] || couleur.couleur) }}
                            </p>
                        </div>
                        <Button variant="secondary" size="sm" @click="remove(couleur)">
                            Supprimer
                        </Button>
                    </div>
                </div>
            </Card>
        </div>
    </AdminLayout>
</template>
