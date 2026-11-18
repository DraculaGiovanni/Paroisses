<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTeamMemberRequest;
use App\Http\Requests\Admin\UpdateTeamMemberRequest;
use App\Models\Paroisse;
use App\Models\User;
use App\Support\Permission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class TeamController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        Gate::authorize('viewAny', User::class);

        $members = User::query()
            ->whereIn('role', ['admin', 'secretariat'])
            ->when(
                ! $request->user()->isSuperAdmin(),
                fn ($query) => $query->where('paroisse_id', $request->user()->paroisse_id)
            )
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role', 'actif', 'permissions', 'last_login_at', 'paroisse_id'])
            ->map(fn (User $member): array => [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'role' => $member->role,
                'actif' => $member->actif,
                'permissions' => $member->effectivePermissions(),
                'last_login_at' => $member->last_login_at?->toIso8601String(),
                'paroisse_id' => $member->paroisse_id,
            ])
            ->values()
            ->all();

        return Inertia::render('Admin/Equipe/Index', [
            'members' => $members,
            'permissionOptions' => Permission::values(),
            'paroisses' => $request->user()->isSuperAdmin()
                ? Paroisse::query()->orderBy('nom')->get(['id', 'nom'])
                : [],
        ]);
    }

    public function store(StoreTeamMemberRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $role = $data['role'] ?? 'secretariat';
        $temporaryPassword = Str::random(20);

        $member = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $role,
            'paroisse_id' => $request->user()->isSuperAdmin()
                ? $data['paroisse_id']
                : $request->user()->paroisse_id,
            'permissions' => $role === 'admin'
                ? Permission::values()
                : ($data['permissions'] ?? Permission::defaultForSecretariat()),
            'password' => $temporaryPassword,
            'must_change_password' => true,
            'actif' => true,
        ]);

        return redirect()
            ->route('admin.equipe.index')
            ->with('temporary_password', $temporaryPassword);
    }

    public function update(UpdateTeamMemberRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        $role = $data['role'] ?? $user->role;

        if ($request->user()->is($user) && $user->isAdmin() && $role !== 'admin') {
            throw ValidationException::withMessages([
                'role' => 'Vous ne pouvez pas retirer votre propre rôle administrateur.',
            ]);
        }

        if ($user->actif && $user->isAdmin() && $role !== 'admin' && $this->activeAdminCount($user) <= 1) {
            throw ValidationException::withMessages([
                'role' => 'La paroisse doit conserver au moins un administrateur actif.',
            ]);
        }

        $user->update([
            'role' => $role,
            'permissions' => $role === 'admin'
                ? Permission::values()
                : ($data['permissions'] ?? (
                    $user->isAdmin()
                        ? Permission::defaultForSecretariat()
                        : ($user->permissions ?? Permission::defaultForSecretariat())
                )),
        ]);

        return redirect()->route('admin.equipe.index')->with('success', 'Membre mis à jour.');
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('update', $user);

        $temporaryPassword = Str::random(20);
        $user->forceFill([
            'password' => $temporaryPassword,
            'must_change_password' => true,
        ])->save();

        return redirect()
            ->route('admin.equipe.index')
            ->with('temporary_password', $temporaryPassword);
    }

    public function toggleActif(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('update', $user);

        if ($user->actif && $user->isAdmin()) {
            if ($request->user()->is($user)) {
                throw ValidationException::withMessages([
                    'actif' => 'Vous ne pouvez pas désactiver votre propre compte administrateur.',
                ]);
            }

            if ($this->activeAdminCount($user) <= 1) {
                throw ValidationException::withMessages([
                    'actif' => 'La paroisse doit conserver au moins un administrateur actif.',
                ]);
            }
        }

        $user->update(['actif' => ! $user->actif]);

        return redirect()->route('admin.equipe.index')->with('success', 'État du compte mis à jour.');
    }

    private function activeAdminCount(User $member): int
    {
        return User::query()
            ->where('paroisse_id', $member->paroisse_id)
            ->where('role', 'admin')
            ->where('actif', true)
            ->count();
    }
}
