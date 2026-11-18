<?php

namespace App\Policies;

use App\Models\Paroisse;
use App\Models\User;

class ParoissePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Super admin peut voir toutes les paroisses
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Paroisse $paroisse): bool
    {
        // Super admin peut voir toutes les paroisses
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Admin/secrétariat ne peut voir que sa propre paroisse
        return $user->paroisse_id === $paroisse->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Seul le super admin peut créer des paroisses
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Paroisse $paroisse): bool
    {
        // Super admin peut modifier toutes les paroisses
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Admin peut modifier sa propre paroisse
        return $user->role === 'admin' && $user->paroisse_id === $paroisse->id;
    }

    /**
     * Determine whether the user can manage users of the paroisse.
     */
    public function manageUsers(User $user, Paroisse $paroisse): bool
    {
        // Admin peut gérer les utilisateurs de sa propre paroisse
        return $user->role === 'admin' && $user->paroisse_id === $paroisse->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Paroisse $paroisse): bool
    {
        // Seul le super admin peut supprimer des paroisses
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Paroisse $paroisse): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Paroisse $paroisse): bool
    {
        return $user->isSuperAdmin();
    }
}
