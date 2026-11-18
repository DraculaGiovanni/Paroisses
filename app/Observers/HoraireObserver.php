<?php

namespace App\Observers;

use App\Models\Horaire;
use Illuminate\Support\Facades\Cache;

class HoraireObserver
{
    /**
     * Invalide le cache de la page publique de la paroisse.
     */
    private function invalidateCache(Horaire $horaire): void
    {
        if ($horaire->paroisse_id) {
            Cache::forget("paroisse:{$horaire->paroisse_id}:public:v3");
        }
    }

    /**
     * Handle the Horaire "created" event.
     */
    public function created(Horaire $horaire): void
    {
        $this->invalidateCache($horaire);
    }

    /**
     * Handle the Horaire "updated" event.
     */
    public function updated(Horaire $horaire): void
    {
        $this->invalidateCache($horaire);
    }

    /**
     * Handle the Horaire "deleted" event.
     */
    public function deleted(Horaire $horaire): void
    {
        $this->invalidateCache($horaire);
    }
}
