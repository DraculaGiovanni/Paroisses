<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || ($user->isAdmin() && $user->paroisse_id !== null);
    }

    public function view(User $user, User $model): Response
    {
        return $this->canManage($user, $model)
            ? Response::allow()
            : $this->denial($user, $model);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || ($user->isAdmin() && $user->paroisse_id !== null);
    }

    public function update(User $user, User $model): Response
    {
        return $this->canManage($user, $model)
            ? Response::allow()
            : $this->denial($user, $model);
    }

    private function canManage(User $user, User $model): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($model->isSuperAdmin()) {
            return false;
        }

        return $user->isAdmin()
            && $user->paroisse_id !== null
            && $user->paroisse_id === $model->paroisse_id;
    }

    private function denial(User $user, User $model): Response
    {
        if ($user->isSuperAdmin()) {
            return Response::deny();
        }

        if ($user->isAdmin() && $user->paroisse_id !== $model->paroisse_id) {
            return Response::denyAsNotFound();
        }

        return Response::deny();
    }
}
