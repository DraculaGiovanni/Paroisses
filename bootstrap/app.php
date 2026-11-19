<?php

use App\Http\Middleware\EnsurePasswordChangeRequired;
use App\Http\Middleware\EnsureUserBelongsToParoisse;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SecureHeaders;
use App\Http\Middleware\ShareInertiaData;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            SecureHeaders::class,
            EnsureUserIsActive::class,
            EnsurePasswordChangeRequired::class,
            ShareInertiaData::class,
        ]);

        // Enregistrer l'alias pour le middleware d'isolation des paroisses
        $middleware->alias([
            'paroisse.isolation' => EnsureUserBelongsToParoisse::class,
        ]);
    })
    ->booted(function (): void {
        RateLimiter::for('intentions.creation', function (Request $request): Limit {
            return Limit::perHour(5)->by($request->ip());
        });

        RateLimiter::for('intentions.suivi', function (Request $request): Limit {
            return Limit::perMinutes(10, 10)->by($request->ip());
        });

        RateLimiter::for('avis.creation', function (Request $request): Limit {
            return Limit::perHour(3)->by($request->ip());
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (ThrottleRequestsException $exception, Request $request) {
            if (! $request->is('p/*/intentions') && ! $request->is('p/*/intentions/suivi') && ! $request->is('p/*/avis')) {
                return null;
            }

            $message = $request->is('p/*/intentions/suivi')
                ? 'Trop de tentatives de suivi. Veuillez réessayer dans quelques minutes.'
                : ($request->is('p/*/avis')
                    ? 'Trop d’avis envoyés. Veuillez réessayer dans une heure.'
                    : 'Trop de demandes de messe envoyées. Veuillez réessayer dans une heure.');

            if ($request->header('X-Inertia') !== null || ! $request->expectsJson()) {
                return back()->withErrors(['rate_limit' => $message])->withInput();
            }

            return response()->json(['message' => $message], 429, $exception->getHeaders());
        });
    })->create();
