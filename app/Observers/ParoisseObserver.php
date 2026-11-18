<?php

namespace App\Observers;

use App\Models\Paroisse;
use Illuminate\Support\Facades\Cache;

class ParoisseObserver
{
    /**
     * Invalide le cache de la page publique de la paroisse.
     */
    private function invalidateCache(Paroisse $paroisse): void
    {
        Cache::forget("paroisse:{$paroisse->id}:public:v3");
    }

    /**
     * Handle the Paroisse "updated" event.
     */
    public function updated(Paroisse $paroisse): void
    {
        $this->invalidateCache($paroisse);
    }
}
