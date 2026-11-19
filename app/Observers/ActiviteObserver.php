<?php

namespace App\Observers;

use App\Models\Activite;
use App\Models\Annonce;
use App\Models\CouleurExceptionnelle;
use App\Models\Horaire;
use App\Models\IntentionMesse;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Journalise les actions des utilisateurs connectés.
 *
 * Seuls l'identifiant de l'utilisateur, le type d'action et la cible sont
 * enregistrés : aucun nom, téléphone, contenu ni adresse IP n'est stocké.
 */
class ActiviteObserver
{
    public function created(Model $model): void
    {
        $this->log($model, 'created');
    }

    public function updated(Model $model): void
    {
        if ($model->wasChanged() === false) {
            return;
        }

        $this->log($model, 'updated');
    }

    public function deleted(Model $model): void
    {
        $this->log($model, 'deleted');
    }

    private function log(Model $model, string $event): void
    {
        $user = auth()->user();

        if ($user === null) {
            return;
        }

        $action = $this->action($model, $event);

        if ($action === null) {
            return;
        }

        $paroisseId = $model->getAttribute('paroisse_id');

        if ($paroisseId === null) {
            return;
        }

        if (! $user->isSuperAdmin() && (int) $paroisseId !== (int) $user->paroisse_id) {
            return;
        }

        Activite::create([
            'paroisse_id' => $paroisseId,
            'user_id' => $user->id,
            'action' => $action,
            'cible_type' => class_basename($model),
            'cible_id' => $model->getKey(),
        ]);
    }

    private function action(Model $model, string $event): ?string
    {
        $actions = match (true) {
            $model instanceof Annonce => [
                'created' => 'annonce.cree',
                'updated' => 'annonce.modifiee',
                'deleted' => 'annonce.supprimee',
            ],
            $model instanceof Horaire => [
                'created' => 'horaire.cree',
                'updated' => 'horaire.modifie',
                'deleted' => 'horaire.supprime',
            ],
            $model instanceof IntentionMesse => [
                'created' => 'intention.cree',
                'updated' => 'intention.modifiee',
                'deleted' => null,
            ],
            $model instanceof CouleurExceptionnelle => [
                'created' => 'couleur.ajoutee',
                'updated' => null,
                'deleted' => 'couleur.supprimee',
            ],
            $model instanceof User => [
                'created' => 'utilisateur.cree',
                'updated' => 'utilisateur.modifie',
                'deleted' => null,
            ],
            default => [],
        };

        return $actions[$event] ?? null;
    }
}
