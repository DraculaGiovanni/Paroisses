<?php

namespace App\Policies;

use App\Models\MessagePrive;
use App\Models\User;
use App\Support\Permission;

class MessagePrivePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin()
            || ($user->paroisse_id !== null
                && ($user->isAdmin() || $user->hasPermission(Permission::MessagesLire)));
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MessagePrive $messagePrive): bool
    {
        return $this->canReadInParoisse($user, $messagePrive);
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
    public function update(User $user, MessagePrive $messagePrive): bool
    {
        return $this->canReadInParoisse($user, $messagePrive);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MessagePrive $messagePrive): bool
    {
        return $user->isSuperAdmin()
            || ($user->paroisse_id !== null
                && $user->paroisse_id === $messagePrive->paroisse_id
                && $user->isAdmin());
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, MessagePrive $messagePrive): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, MessagePrive $messagePrive): bool
    {
        return false;
    }

    private function canReadInParoisse(User $user, MessagePrive $messagePrive): bool
    {
        return $user->isSuperAdmin()
            || ($user->paroisse_id !== null
                && $user->paroisse_id === $messagePrive->paroisse_id
                && ($user->isAdmin() || $user->hasPermission(Permission::MessagesLire)));
    }
}
