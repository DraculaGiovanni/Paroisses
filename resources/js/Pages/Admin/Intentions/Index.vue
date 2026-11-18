<template>
    <Head title="Intentions de messe" />

    <AdminLayout>
        <PageHeader
            title="Intentions de messe"
            :description="nouvelles > 0
                ? `${nouvelles} nouvelle(s) demande(s) à traiter.`
                : 'Toutes les demandes ont été traitées.'"
        >
            <template #actions>
                <Button
                    :href="route('admin.intentions.feuille')"
                    variant="secondary"
                >
                    Feuille des intentions
                </Button>
            </template>
        </PageHeader>

        <div class="mb-4 grid gap-3 sm:grid-cols-2">
            <Select
                v-model="filters.statut"
                label="Statut"
                :options="statutOptions"
                @update:model-value="appliquerFiltres"
            />
            <Input
                v-model="filters.date"
                type="date"
                label="Date souhaitée"
                @change="appliquerFiltres"
            />
        </div>

        <div v-if="intentions.data.length > 0" class="grid gap-4 xl:grid-cols-2">
            <Card v-for="intention in intentions.data" :key="intention.id" hover>
                <div class="p-5 sm:p-6">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ intention.nom_demandeur }}
                                </h3>
                                <Badge :variant="varianteStatut(intention.statut)" size="sm">
                                    {{ intention.statut_label }}
                                </Badge>
                            </div>

                            <p class="mt-1 font-mono text-sm tracking-wider text-spiritual-700 dark:text-spiritual-300">
                                {{ intention.code_suivi }}
                            </p>

                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                {{ intention.type_label }}
                                <span v-if="intention.date_souhaitee">
                                    — souhaitée le {{ formatDate(intention.date_souhaitee) }}
                                </span>
                            </p>

                            <p v-if="intention.date_programmee" class="mt-1 text-sm text-green-700 dark:text-green-300">
                                Programmée le {{ formatDate(intention.date_programmee) }}
                                <span v-if="intention.horaire_programme"> — {{ intention.horaire_programme }}</span>
                            </p>

                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-500">
                                Reçue le {{ formatDate(intention.created_at) }}
                            </p>
                        </div>

                        <Button
                            :href="route('admin.intentions.show', intention.id)"
                            variant="primary"
                            size="sm"
                        >
                            Ouvrir
                        </Button>
                    </div>
                </div>
            </Card>

            <div v-if="intentions.prev_page_url || intentions.next_page_url" class="flex items-center justify-between pt-4">
                <Button
                    v-if="intentions.prev_page_url"
                    :href="intentions.prev_page_url"
                    variant="secondary"
                >
                    Précédent
                </Button>
                <div v-else></div>

                <Button
                    v-if="intentions.next_page_url"
                    :href="intentions.next_page_url"
                    variant="secondary"
                >
                    Suivant
                </Button>
            </div>
        </div>

        <EmptyState
            v-else
            title="Aucune demande"
            description="Aucune demande de messe ne correspond à ces filtres pour le moment."
        />
    </AdminLayout>
</template>

<script setup>
import { reactive } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';
import Badge from '@/Components/UI/Badge.vue';
import Select from '@/Components/UI/Select.vue';
import Input from '@/Components/UI/Input.vue';
import EmptyState from '@/Components/UI/EmptyState.vue';

const props = defineProps({
    intentions: { type: Object, required: true },
    statuts: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({ statut: '', date: '' }) },
    nouvelles: { type: Number, default: 0 },
    aujourdhui: { type: String, default: '' },
});

const statutOptions = [
    { value: '', label: 'Tous les statuts' },
    ...props.statuts,
];

const filters = reactive({
    statut: props.filters?.statut || '',
    date: props.filters?.date || '',
});

const VARIANTES = {
    recue: 'info',
    confirmee: 'info',
    programmee: 'success',
    celebree: 'success',
    refusee: 'danger',
    annulee: 'warning',
};

function varianteStatut(statut) {
    return VARIANTES[statut] || 'default';
}

function appliquerFiltres() {
    router.get(route('admin.intentions.index'), {
        statut: filters.statut || undefined,
        date: filters.date || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function formatDate(value) {
    if (!value) {
        return '';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return date.toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
}
</script>
