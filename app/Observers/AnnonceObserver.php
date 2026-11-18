<?php

namespace App\Observers;

use App\Models\Annonce;
use Illuminate\Support\Facades\Cache;

class AnnonceObserver
{
    /**
     * Invalide le cache de la page publique de la paroisse.
     */
    private function invalidateCache(Annonce $annonce): void
    {
        if ($annonce->paroisse_id) {
            Cache::forget("paroisse:{$annonce->paroisse_id}:public:v3");
        }
    }

    /**
     * Handle the Annonce "created" event.
     */
    public function created(Annonce $annonce): void
    {
        $this->invalidateCache($annonce);
    }

    /**
     * Handle the Annonce "updated" event.
     */
    public function updated(Annonce $annonce): void
    {
        $this->invalidateCache($annonce);
    }

    /**
     * Handle the Annonce "deleted" event.
     */
    public function deleted(Annonce $annonce): void
    {
        $this->invalidateCache($annonce);
    }
}
