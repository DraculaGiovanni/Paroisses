<?php

namespace App\Policies;

use App\Models\IntentionMesse;
use App\Models\User;
use App\Support\Permission;

class IntentionMessePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin()
            || ($user->paroisse_id !== null
                && ($user->isAdmin() || $user->hasPermission(Permission::IntentionsGerer)));
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, IntentionMesse $intentionMesse): bool
    {
        return $user->isSuperAdmin()
            || ($user->paroisse_id !== null
                && $user->paroisse_id === $intentionMesse->paroisse_id
                && ($user->isAdmin() || $user->hasPermission(Permission::IntentionsGerer)));
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, IntentionMesse $intentionMesse): bool
    {
        return $this->view($user, $intentionMesse);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, IntentionMesse $intentionMesse): bool
    {
        return $this->view($user, $intentionMesse);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, IntentionMesse $intentionMesse): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, IntentionMesse $intentionMesse): bool
    {
        return false;
    }
}
