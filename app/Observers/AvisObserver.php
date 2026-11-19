<?php

namespace App\Observers;

use App\Models\Avis;
use Illuminate\Support\Facades\Cache;

class AvisObserver
{
    public function saved(Avis $avis): void
    {
        Cache::forget("paroisse:{$avis->paroisse_id}:public:v3");
    }

    public function deleted(Avis $avis): void
    {
        Cache::forget("paroisse:{$avis->paroisse_id}:public:v3");
    }
}
