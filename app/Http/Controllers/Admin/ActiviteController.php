<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activite;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ActiviteController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Activite::class);

        $now = Carbon::now('Africa/Porto-Novo');

        $activites = Activite::query()
            ->with('user:id,name,role')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Activite $activite): array => [
                'id' => $activite->id,
                'action' => $activite->action,
                'label' => $activite->label(),
                'utilisateur' => $activite->user?->name ?? 'Système',
                'role' => $activite->user?->role,
                'cible_type' => $activite->cible_type,
                'created_at' => $activite->created_at?->format('d/m/Y H:i'),
            ]);

        $utilisateursQuery = User::query();

        if (! $request->user()->isSuperAdmin()) {
            $utilisateursQuery->where('paroisse_id', $request->user()->paroisse_id);
        }

        $utilisateurs = $utilisateursQuery
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role', 'created_at'])
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'role_label' => match ($user->role) {
                    'super_admin' => 'Super administrateur',
                    'admin' => 'Administrateur',
                    default => 'Secrétariat',
                },
                'created_at' => $user->created_at?->format('d/m/Y'),
            ])->all();

        return Inertia::render('Admin/Activites/Index', [
            'activites' => $activites,
            'utilisateurs' => $utilisateurs,
            'stats' => [
                'utilisateurs' => count($utilisateurs),
                'actions_aujourdhui' => Activite::query()->whereDate('created_at', $now->toDateString())->count(),
                'actions_7_jours' => Activite::query()->where('created_at', '>=', $now->copy()->subDays(7))->count(),
            ],
            'peut_voir_toutes_les_paroisses' => $request->user()->isSuperAdmin(),
        ]);
    }
}
