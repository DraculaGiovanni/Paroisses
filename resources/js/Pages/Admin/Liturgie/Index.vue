<script setup>
import { ref } from 'vue';
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
        <PageHeader title="Couleurs exceptionnelles" />

        <div class="grid gap-4 sm:gap-6 lg:grid-cols-2">
            <Card>
                <form @submit.prevent="submit" class="p-6 space-y-4">
                    <Input v-model="form.date" type="date" label="Date" :error="form.errors.date" required />
                    <Select v-model="form.couleur" label="Couleur" :options="colorOptions" :error="form.errors.couleur" required />
                    <Input v-model="form.libelle" label="Libellé (optionnel)" :error="form.errors.libelle" />
                    <Button type="submit" variant="primary" :loading="form.processing">Ajouter</Button>
                </form>
            </Card>

            <Card>
                <div class="p-6 space-y-3">
                    <div v-if="couleurs.length === 0" class="text-sm text-gray-500">
                        Aucune couleur exceptionnelle.
                    </div>
                    <div
                        v-for="couleur in couleurs"
                        :key="couleur.id"
                        class="flex items-center justify-between gap-3 rounded-lg border border-cream-300 p-3 dark:border-gray-700"
                    >
                        <div>
                            <p class="font-medium">{{ couleur.date }}</p>
                            <p class="text-sm text-gray-500">{{ couleur.libelle || couleur.couleur }}</p>
                        </div>
                        <Button variant="danger" size="sm" @click="remove(couleur)">Supprimer</Button>
                    </div>
                </div>
            </Card>
        </div>
    </AdminLayout>
</template>
