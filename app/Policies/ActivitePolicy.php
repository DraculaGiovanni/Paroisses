<?php

namespace App\Policies;

use App\Models\Activite;
use App\Models\User;

class ActivitePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || ($user->paroisse_id !== null && $user->isAdmin());
    }

    public function view(User $user, Activite $activite): bool
    {
        return $user->isSuperAdmin()
            || ($user->paroisse_id !== null
                && $user->paroisse_id === $activite->paroisse_id
                && $user->isAdmin());
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Activite $activite): bool
    {
        return false;
    }

    public function delete(User $user, Activite $activite): bool
    {
        return false;
    }
}
