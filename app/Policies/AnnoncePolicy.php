<?php

namespace App\Policies;

use App\Models\Annonce;
use App\Models\User;
use App\Support\Permission;

class AnnoncePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin()
            || ($user->paroisse_id !== null
                && ($user->isAdmin() || $user->hasPermission(Permission::AnnoncesGerer)));
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Annonce $annonce): bool
    {
        return $user->paroisse_id === $annonce->paroisse_id
            && ($user->isAdmin() || $user->hasPermission(Permission::AnnoncesGerer));
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->paroisse_id !== null
            && ($user->isAdmin() || $user->hasPermission(Permission::AnnoncesGerer));
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Annonce $annonce): bool
    {
        return $user->paroisse_id === $annonce->paroisse_id
            && ($user->isAdmin() || $user->hasPermission(Permission::AnnoncesGerer));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Annonce $annonce): bool
    {
        return $user->isAdmin() && $user->paroisse_id === $annonce->paroisse_id;
    }
}
