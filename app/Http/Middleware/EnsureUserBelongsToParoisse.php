<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserBelongsToParoisse
{
    /**
     * Handle an incoming request.
     *
     * S'assure que l'utilisateur ne peut accéder qu'aux ressources de sa propre paroisse.
     * Les super_admin peuvent accéder à toutes les paroisses.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Les super admin peuvent tout voir
        if ($user && $user->isSuperAdmin()) {
            return $next($request);
        }

        // Les admin et secrétariat doivent avoir une paroisse
        if (! $user || ! $user->paroisse_id) {
            abort(403, 'Vous devez être rattaché à une paroisse.');
        }

        // Si la route contient un paramètre de modèle avec paroisse_id, on vérifie
        $routeParams = $request->route()->parameters();

        foreach ($routeParams as $param) {
            // Si c'est un modèle Eloquent avec une colonne paroisse_id
            if (is_object($param) && method_exists($param, 'getAttribute')) {
                $modelParoisseId = $param->getAttribute('paroisse_id');

                // Si le modèle a une paroisse_id différente de celle de l'utilisateur
                if ($modelParoisseId !== null && $modelParoisseId != $user->paroisse_id) {
                    if ($param instanceof User) {
                        abort(404);
                    }

                    abort(403, 'Vous ne pouvez pas accéder aux ressources d\'une autre paroisse.');
                }
            }
        }

        return $next($request);
    }
}
