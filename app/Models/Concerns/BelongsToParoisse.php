<?php

namespace App\Models\Concerns;

use App\Models\Paroisse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Trait BelongsToParoisse
 *
 * Ajoute automatiquement un global scope pour filtrer les modèles par paroisse
 * de l'utilisateur connecté, et remplit automatiquement paroisse_id à la création.
 *
 * Les lectures sont filtrées uniquement pour les utilisateurs authentifiés :
 * - super_admin : aucune restriction ;
 * - utilisateur avec paroisse_id : sa paroisse uniquement ;
 * - autre utilisateur sans paroisse_id : aucune ligne ;
 * - visiteur, console, jobs, seeders et observers : aucun filtre automatique.
 *
 * Dans les accès publics et les traitements en arrière-plan, utiliser
 * forParoisse($id), qui retire ce scope global et applique explicitement le tenant.
 * À la création, une paroisse explicite est requise hors requête authentifiée ;
 * un utilisateur normal ne peut fournir que sa propre paroisse.
 */
trait BelongsToParoisse
{
    protected static function bootBelongsToParoisse(): void
    {
        static::addGlobalScope('paroisse', function (Builder $query) {
            $user = auth()->user();

            if (! $user) {
                return;
            }

            if ($user->role === 'super_admin') {
                return;
            }

            if (! $user->paroisse_id) {
                $query->whereRaw('1 = 0');

                return;
            }

            $query->where($query->getModel()->getTable().'.paroisse_id', $user->paroisse_id);
        });

        static::creating(function ($model) {
            $user = auth()->user();
            $paroisseId = $model->getAttribute('paroisse_id');

            if ($user && $user->role === 'super_admin') {
                if (! $paroisseId) {
                    throw new \RuntimeException(
                        'Impossible de créer une ressource : une paroisse_id explicite est requise.'
                    );
                }

                return;
            }

            if ($user && ! $user->paroisse_id) {
                throw new \RuntimeException(
                    'Impossible de créer une ressource : l\'utilisateur n\'est rattaché à aucune paroisse.'
                );
            }

            if ($user) {
                if ($paroisseId && (int) $paroisseId !== (int) $user->paroisse_id) {
                    throw new \RuntimeException(
                        'Impossible de créer une ressource : la paroisse_id ne correspond pas à celle de l\'utilisateur.'
                    );
                }

                $model->paroisse_id = $user->paroisse_id;

                return;
            }

            if (! $paroisseId) {
                throw new \RuntimeException(
                    'Impossible de créer une ressource : une paroisse_id explicite est requise hors authentification.'
                );
            }
        });
    }

    public function scopeForParoisse(Builder $query, int $paroisseId): Builder
    {
        return $query->withoutGlobalScope('paroisse')
            ->where($query->getModel()->qualifyColumn('paroisse_id'), $paroisseId);
    }

    public function paroisse(): BelongsTo
    {
        return $this->belongsTo(Paroisse::class);
    }
}
