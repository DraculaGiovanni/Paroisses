<?php

namespace App\Providers;

use App\Models\Annonce;
use App\Models\CouleurExceptionnelle;
use App\Models\Horaire;
use App\Models\IntentionMesse;
use App\Models\MessagePrive;
use App\Models\Paroisse;
use App\Models\User;
use App\Observers\AnnonceObserver;
use App\Observers\HoraireObserver;
use App\Observers\ParoisseObserver;
use App\Policies\CouleurExceptionnellePolicy;
use App\Policies\IntentionMessePolicy;
use App\Policies\MessagePrivePolicy;
use App\Policies\UserPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Gate pour la gestion des paroisses (super admin uniquement)
        Gate::define('manage-paroisses', fn (User $user) => $user->isSuperAdmin());
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(CouleurExceptionnelle::class, CouleurExceptionnellePolicy::class);
        Gate::policy(IntentionMesse::class, IntentionMessePolicy::class);
        Gate::policy(MessagePrive::class, MessagePrivePolicy::class);

        // Enregistrement des observers pour invalidation du cache
        Horaire::observe(HoraireObserver::class);
        Annonce::observe(AnnonceObserver::class);
        Paroisse::observe(ParoisseObserver::class);

        // Monitoring des performances en environnement local uniquement
        if ($this->app->environment('local')) {
            // Mode strict : détecte lazy loading, mass assignment non protégé, etc.
            Model::shouldBeStrict();

            // Log des requêtes lentes (> 100ms)
            DB::listen(function ($query) {
                if ($query->time > 100) {
                    Log::channel('daily')->warning('Slow query detected', [
                        'sql' => $query->sql,
                        'bindings_count' => count($query->bindings),
                        'time' => $query->time.'ms',
                    ]);
                }
            });
        }
    }
}
