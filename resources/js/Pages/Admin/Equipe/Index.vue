<script setup>
import { computed, ref } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Card from '@/Components/UI/Card.vue';
import Input from '@/Components/UI/Input.vue';
import Select from '@/Components/UI/Select.vue';
import Button from '@/Components/UI/Button.vue';
import Badge from '@/Components/UI/Badge.vue';
import Modal from '@/Components/UI/Modal.vue';

const props = defineProps({
    members: { type: Array, default: () => [] },
    permissionOptions: { type: Array, default: () => [] },
    paroisses: { type: Array, default: () => [] },
});

const page = usePage();
const estSuperAdmin = computed(() => page.props.auth.user.role === 'super_admin');
const motDePasseTemporaire = computed(() => page.props.flash?.temporary_password || null);

const permissionLabels = {
    'annonces.gerer': 'Annonces',
    'horaires.gerer': 'Horaires',
    'intentions.gerer': 'Demandes de messe',
    'quetes.gerer': 'Quêtes',
    'sacrements.gerer': 'Sacrements',
    'liturgie.gerer': 'Liturgie',
    'messages.lire': 'Messages privés',
};

const roleOptions = [
    { value: 'secretariat', label: 'Secrétaire' },
    { value: 'admin', label: 'Administrateur' },
];

const paroisseOptions = computed(() => [
    { value: '', label: 'Choisir une paroisse' },
    ...props.paroisses.map((paroisse) => ({ value: paroisse.id, label: paroisse.nom })),
]);

const form = useForm({
    name: '',
    email: '',
    role: 'secretariat',
    permissions: ['annonces.gerer', 'horaires.gerer', 'intentions.gerer'],
    paroisse_id: '',
});

const editing = ref(null);
const editForm = useForm({
    role: 'secretariat',
    permissions: [],
});

function creerMembre() {
    form.transform((data) => {
        if (estSuperAdmin.value) {
            return data;
        }

        const { paroisse_id, ...reste } = data;

        return reste;
    }).post(route('admin.equipe.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}

function ouvrirModification(member) {
    editing.value = member;
    editForm.role = member.role;
    editForm.permissions = [...(member.permissions || [])];
    editForm.clearErrors();
}

function enregistrerModification() {
    editForm.put(route('admin.equipe.update', editing.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            editing.value = null;
        },
    });
}

function basculerActif(member) {
    router.patch(route('admin.equipe.toggle-actif', member.id), {}, { preserveScroll: true });
}

function reinitialiserMotDePasse(member) {
    router.post(route('admin.equipe.reset-password', member.id), {}, { preserveScroll: true });
}

function basculerPermission(cible, permission) {
    const index = cible.indexOf(permission);

    if (index === -1) {
        cible.push(permission);
    } else {
        cible.splice(index, 1);
    }
}

function libellePermission(permission) {
    return permissionLabels[permission] || permission;
}

function libelleRole(role) {
    return role === 'admin' ? 'Administrateur' : 'Secrétaire';
}
</script>

<template>
    <Head title="Équipe de la paroisse" />

    <AdminLayout>
        <PageHeader
            title="Équipe de la paroisse"
            description="Créez les comptes des secrétaires et choisissez leurs permissions."
        />

        <div
            v-if="motDePasseTemporaire"
            class="mb-6 rounded-xl border border-gold-500 bg-gold-50 p-4 text-sm text-spiritual-900 dark:bg-gold-500/10 dark:text-gold-200"
        >
            <p class="font-semibold">Mot de passe temporaire — à noter maintenant, il ne sera plus affiché.</p>
            <p class="mt-2 break-all font-mono text-base font-bold">{{ motDePasseTemporaire }}</p>
            <p class="mt-2">
                Transmettez-le au membre concerné. Un changement de mot de passe sera demandé à sa première connexion.
            </p>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <Card>
                <form class="space-y-4 p-6" @submit.prevent="creerMembre">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Ajouter un membre</h2>

                    <Input v-model="form.name" label="Nom complet" :error="form.errors.name" required />
                    <Input v-model="form.email" type="email" label="Adresse e-mail" :error="form.errors.email" required />
                    <Select
                        v-model="form.role"
                        label="Rôle"
                        :options="roleOptions"
                        :error="form.errors.role"
                        required
                    />
                    <Select
                        v-if="estSuperAdmin"
                        v-model="form.paroisse_id"
                        label="Paroisse"
                        :options="paroisseOptions"
                        :error="form.errors.paroisse_id"
                        required
                    />

                    <fieldset>
                        <legend class="mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Permissions
                        </legend>
                        <p v-if="form.role === 'admin'" class="mb-2 text-xs text-gray-500">
                            Un administrateur dispose de toutes les permissions.
                        </p>
                        <div class="grid gap-2 sm:grid-cols-2">
                            <label
                                v-for="permission in permissionOptions"
                                :key="permission"
                                class="flex min-h-11 items-center gap-2 text-sm text-gray-700 dark:text-gray-300"
                            >
                                <input
                                    type="checkbox"
                                    class="rounded border-cream-300 text-spiritual-700 focus:ring-spiritual-600"
                                    :checked="form.permissions.includes(permission)"
                                    :disabled="form.role === 'admin'"
                                    @change="basculerPermission(form.permissions, permission)"
                                />
                                {{ libellePermission(permission) }}
                            </label>
                        </div>
                        <p v-if="form.errors.permissions" class="mt-1.5 text-sm text-red-600">
                            {{ form.errors.permissions }}
                        </p>
                    </fieldset>

                    <Button type="submit" variant="primary" :loading="form.processing" fullWidth>
                        Créer le compte
                    </Button>
                </form>
            </Card>

            <Card>
                <div class="space-y-3 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Membres ({{ members.length }})
                    </h2>

                    <p v-if="members.length === 0" class="text-sm text-gray-500">
                        Aucun membre pour le moment.
                    </p>

                    <div
                        v-for="member in members"
                        :key="member.id"
                        class="rounded-lg border border-cream-300 p-4 dark:border-gray-700"
                    >
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <div class="min-w-0">
                                <p class="font-medium text-gray-900 dark:text-white">{{ member.name }}</p>
                                <p class="truncate text-sm text-gray-500">{{ member.email }}</p>
                            </div>
                            <div class="flex gap-2">
                                <Badge :variant="member.role === 'admin' ? 'info' : 'default'" size="sm">
                                    {{ libelleRole(member.role) }}
                                </Badge>
                                <Badge :variant="member.actif ? 'success' : 'warning'" size="sm">
                                    {{ member.actif ? 'Actif' : 'Inactif' }}
                                </Badge>
                            </div>
                        </div>

                        <p class="mt-2 text-xs text-gray-500">
                            Permissions : {{ member.permissions.map(libellePermission).join(', ') || 'aucune' }}
                        </p>
                        <p v-if="member.last_login_at" class="mt-1 text-xs text-gray-400">
                            Dernière connexion : {{ new Date(member.last_login_at).toLocaleDateString('fr-FR') }}
                        </p>

                        <div class="mt-3 flex flex-wrap gap-2">
                            <Button variant="secondary" size="sm" @click="ouvrirModification(member)">
                                Modifier
                            </Button>
                            <Button variant="ghost" size="sm" @click="reinitialiserMotDePasse(member)">
                                Réinitialiser le mot de passe
                            </Button>
                            <Button variant="ghost" size="sm" @click="basculerActif(member)">
                                {{ member.actif ? 'Désactiver' : 'Activer' }}
                            </Button>
                        </div>
                    </div>
                </div>
            </Card>
        </div>

        <Modal
            :show="editing !== null"
            title="Modifier le membre"
            @close="editing = null"
            @update:show="!$event && (editing = null)"
        >
            <form v-if="editing" class="space-y-4" @submit.prevent="enregistrerModification">
                <Select
                    v-model="editForm.role"
                    label="Rôle"
                    :options="roleOptions"
                    :error="editForm.errors.role"
                    required
                />

                <fieldset>
                    <legend class="mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">Permissions</legend>
                    <div class="grid gap-2 sm:grid-cols-2">
                        <label
                            v-for="permission in permissionOptions"
                            :key="permission"
                            class="flex min-h-11 items-center gap-2 text-sm text-gray-700 dark:text-gray-300"
                        >
                            <input
                                type="checkbox"
                                class="rounded border-cream-300 text-spiritual-700 focus:ring-spiritual-600"
                                :checked="editForm.permissions.includes(permission)"
                                :disabled="editForm.role === 'admin'"
                                @change="basculerPermission(editForm.permissions, permission)"
                            />
                            {{ libellePermission(permission) }}
                        </label>
                    </div>
                </fieldset>

                <div class="flex justify-end gap-3 pt-2">
                    <Button type="button" variant="secondary" @click="editing = null">Annuler</Button>
                    <Button type="submit" variant="primary" :loading="editForm.processing">Enregistrer</Button>
                </div>
            </form>
        </Modal>
    </AdminLayout>
</template>
