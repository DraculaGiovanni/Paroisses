<?php

namespace App\Http\Middleware;

use App\Models\IntentionMesse;
use App\Support\Permission;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShareInertiaData
{
    public function handle(Request $request, Closure $next)
    {
        Inertia::share([
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'role' => $request->user()->role,
                    'paroisse_id' => $request->user()->paroisse_id,
                    'permissions' => $request->user()->effectivePermissions(),
                ] : null,
            ],
            'paroisse' => function () use ($request) {
                $user = $request->user();
                if ($user && $user->paroisse_id) {
                    $paroisse = $user->paroisse;

                    return $paroisse ? [
                        'id' => $paroisse->id,
                        'nom' => $paroisse->nom,
                        'slug' => $paroisse->slug,
                        'couleur_principale' => $paroisse->couleur_principale,
                    ] : null;
                }

                return null;
            },
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'temporary_password' => fn () => $request->session()->get('temporary_password'),
            ],
            'menu' => [
                'intentions_nouvelles' => function () use ($request): int {
                    $user = $request->user();

                    if ($user === null || $user->paroisse_id === null) {
                        return 0;
                    }

                    if (! $user->isAdmin() && ! $user->hasPermission(Permission::IntentionsGerer)) {
                        return 0;
                    }

                    return IntentionMesse::query()
                        ->where('paroisse_id', $user->paroisse_id)
                        ->where('statut', 'recue')
                        ->count();
                },
            ],
        ]);

        return $next($request);
    }
}
