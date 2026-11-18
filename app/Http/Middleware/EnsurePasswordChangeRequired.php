<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChangeRequired
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $routeName = $request->route()?->getName();
        $allowedRoutes = [
            'password.force',
            'password.force.update',
            'logout',
        ];

        if ($user !== null && $user->must_change_password && ! in_array($routeName, $allowedRoutes, true)) {
            return redirect()->route('password.force');
        }

        return $next($request);
    }
}
