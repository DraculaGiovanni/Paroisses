<template>
    <Head :title="`Demande ${intention.code_suivi}`" />

    <AdminLayout>
        <PageHeader
            :title="intention.nom_demandeur"
            :description="`Demande ${intention.code_suivi} — ${intention.type_label}`"
        >
            <template #actions>
                <Button :href="route('admin.intentions.index')" variant="ghost">
                    Retour à la liste
                </Button>
                <Button
                    v-if="whatsapp_url"
                    :href="whatsapp_url"
                    variant="gold"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    Écrire sur WhatsApp
                </Button>
            </template>
        </PageHeader>

        <div class="grid gap-4 lg:grid-cols-3">
            <Card class="lg:col-span-2">
                <div class="p-5 sm:p-6">
                    <div class="flex flex-wrap items-center gap-3">
                        <Badge :variant="varianteStatut(intention.statut)">
                            {{ intention.statut_label }}
                        </Badge>
                        <span class="font-mono text-sm tracking-wider text-spiritual-700 dark:text-spiritual-300">
                            {{ intention.code_suivi }}
                        </span>
                    </div>

                    <dl class="mt-5 grid gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Téléphone</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ intention.telephone || 'Non renseigné' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Date souhaitée</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ intention.date_souhaitee ? formatDate(intention.date_souhaitee) : 'Aucune préférence' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Messe souhaitée</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ intention.horaire_souhaite || 'Aucune préférence' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Demande reçue le</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ formatDate(intention.created_at) }}
                            </dd>
                        </div>
                    </dl>

                    <div class="mt-5 border-t border-cream-200 pt-4 dark:border-gray-700">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Intention
                        </h2>
                        <p class="mt-2 whitespace-pre-line text-gray-900 dark:text-white">
                            {{ intention.intention }}
                        </p>
                    </div>
                </div>
            </Card>

            <Card>
                <div class="p-5 sm:p-6">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        Programmation
                    </h2>

                    <dl class="mt-3 space-y-3 text-sm">
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Date</dt>
                            <dd class="font-medium text-gray-900 dark:text-white">
                                {{ intention.date_programmee ? formatDate(intention.date_programmee) : 'Non programmée' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Messe</dt>
                            <dd class="font-medium text-gray-900 dark:text-white">
                                {{ intention.horaire_programme || 'Non précisée' }}
                            </dd>
                        </div>
                        <div v-if="intention.motif_refus">
                            <dt class="text-gray-500 dark:text-gray-400">Motif du refus</dt>
                            <dd class="font-medium text-red-700 dark:text-red-300">
                                {{ intention.motif_refus }}
                            </dd>
                        </div>
                        <div v-if="intention.traitee_par">
                            <dt class="text-gray-500 dark:text-gray-400">Traitée par</dt>
                            <dd class="font-medium text-gray-900 dark:text-white">
                                {{ intention.traitee_par }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </Card>
        </div>

        <Card class="mt-4">
            <div class="p-5 sm:p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Actions
                </h2>

                <div v-if="transitions.length > 0" class="mt-4 flex flex-wrap gap-3">
                    <Button
                        v-if="peut('confirmee')"
                        variant="primary"
                        :loading="form.processing && form.statut === 'confirmee'"
                        @click="transitionSimple('confirmee')"
                    >
                        Confirmer
                    </Button>

                    <Button
                        v-if="peut('programmee')"
                        variant="primary"
                        @click="ouvrirProgrammation"
                    >
                        Programmer
                    </Button>

                    <Button
                        v-if="peut('celebree')"
                        variant="secondary"
                        :loading="form.processing && form.statut === 'celebree'"
                        @click="transitionSimple('celebree')"
                    >
                        Marquer célébrée
                    </Button>

                    <Button
                        v-if="peut('refusee')"
                        variant="danger"
                        @click="ouvrirRefus"
                    >
                        Refuser
                    </Button>

                    <Button
                        v-if="peut('annulee')"
                        variant="ghost"
                        @click="showAnnulation = true"
                    >
                        Annuler la demande
                    </Button>
                </div>

                <p v-else class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                    Cette demande est clôturée : aucune transition n'est possible.
                </p>

                <div
                    v-if="panneau === 'programmer'"
                    class="mt-5 rounded-xl border border-cream-300 bg-cream-50 p-4 dark:border-gray-700 dark:bg-gray-800"
                >
                    <h3 class="font-semibold text-gray-900 dark:text-white">Programmer la demande</h3>

                    <div class="mt-3 grid gap-4 sm:grid-cols-2">
                        <Input
                            v-model="form.date_programmee"
                            type="date"
                            label="Date de la messe"
                            :min="aujourdhui"
                            :error="form.errors.date_programmee"
                            required
                        />
                        <Select
                            v-model="form.horaire_programme_id"
                            label="Messe"
                            placeholder="Choisissez une messe"
                            :options="horaireOptions"
                            :error="form.errors.horaire_programme_id"
                            required
                        />
                    </div>

                    <p v-if="form.errors.statut" class="mt-2 text-sm text-red-600 dark:text-red-400">
                        {{ form.errors.statut }}
                    </p>

                    <div class="mt-4 flex flex-wrap gap-3">
                        <Button
                            variant="primary"
                            :loading="form.processing"
                            :disabled="form.processing"
                            @click="soumettre"
                        >
                            Enregistrer la programmation
                        </Button>
                        <Button variant="ghost" @click="fermerPanneau">Annuler</Button>
                    </div>
                </div>

                <div
                    v-if="panneau === 'refuser'"
                    class="mt-5 rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-900/50 dark:bg-red-950/30"
                >
                    <h3 class="font-semibold text-red-900 dark:text-red-200">Refuser la demande</h3>

                    <div class="mt-3">
                        <Textarea
                            v-model="form.motif_refus"
                            label="Motif du refus"
                            placeholder="Expliquez brièvement le motif…"
                            :maxlength="200"
                            :rows="3"
                            :error="form.errors.motif_refus"
                            required
                        />
                    </div>

                    <p v-if="form.errors.statut" class="mt-2 text-sm text-red-700 dark:text-red-300">
                        {{ form.errors.statut }}
                    </p>

                    <div class="mt-4 flex flex-wrap gap-3">
                        <Button
                            variant="danger"
                            :loading="form.processing"
                            :disabled="form.processing"
                            @click="soumettre"
                        >
                            Confirmer le refus
                        </Button>
                        <Button variant="ghost" @click="fermerPanneau">Annuler</Button>
                    </div>
                </div>
            </div>
        </Card>

        <ConfirmDialog
            v-model:show="showAnnulation"
            title="Annuler la demande"
            message="Êtes-vous sûr de vouloir annuler cette demande ? Cette action est définitive."
            variant="danger"
            @confirm="confirmerAnnulation"
            @cancel="showAnnulation = false"
        />
    </AdminLayout>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';
import Badge from '@/Components/UI/Badge.vue';
import Input from '@/Components/UI/Input.vue';
import Select from '@/Components/UI/Select.vue';
import Textarea from '@/Components/UI/Textarea.vue';
import ConfirmDialog from '@/Components/UI/ConfirmDialog.vue';

const props = defineProps({
    intention: { type: Object, required: true },
    transitions: { type: Array, default: () => [] },
    horaires: { type: Array, default: () => [] },
    aujourdhui: { type: String, required: true },
    whatsapp_url: { type: String, default: null },
});

const VARIANTES = {
    recue: 'info',
    confirmee: 'info',
    programmee: 'success',
    celebree: 'success',
    refusee: 'danger',
    annulee: 'warning',
};

const panneau = ref(null);
const showAnnulation = ref(false);

const form = useForm({
    statut: '',
    date_programmee: '',
    horaire_programme_id: '',
    motif_refus: '',
});

const horaireOptions = computed(() => props.horaires);

function varianteStatut(statut) {
    return VARIANTES[statut] || 'default';
}

function peut(statut) {
    return props.transitions.some((transition) => transition.value === statut);
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

function reinitialiser() {
    form.reset();
    form.clearErrors();
    panneau.value = null;
}

function ouvrirProgrammation() {
    reinitialiser();
    form.statut = 'programmee';
    panneau.value = 'programmer';
}

function ouvrirRefus() {
    reinitialiser();
    form.statut = 'refusee';
    panneau.value = 'refuser';
}

function fermerPanneau() {
    reinitialiser();
}

function transitionSimple(statut) {
    reinitialiser();
    form.statut = statut;
    soumettre();
}

function confirmerAnnulation() {
    showAnnulation.value = false;
    transitionSimple('annulee');
}

function soumettre() {
    form.patch(route('admin.intentions.statut', props.intention.id), {
        preserveScroll: true,
        onSuccess: () => reinitialiser(),
    });
}
</script>
