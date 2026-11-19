<?php

namespace App\Policies;

use App\Models\Avis;
use App\Models\User;

class AvisPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || ($user->paroisse_id !== null && $user->isAdmin());
    }

    public function view(User $user, Avis $avis): bool
    {
        return $user->isSuperAdmin()
            || ($user->paroisse_id !== null
                && $user->paroisse_id === $avis->paroisse_id
                && $user->isAdmin());
    }

    public function update(User $user, Avis $avis): bool
    {
        return $this->view($user, $avis);
    }

    public function delete(User $user, Avis $avis): bool
    {
        return $this->view($user, $avis);
    }
}
