<?php

namespace App\Actions\Annonce;

use App\Models\Annonce;

class PublishAnnonce
{
    /**
     * Publie une annonce immédiatement.
     *
     * Définit la date de publication à maintenant si pas déjà définie.
     * Active l'annonce par défaut.
     *
     * @param  Annonce  $annonce  L'annonce à publier
     * @return Annonce L'annonce mise à jour
     */
    public function execute(Annonce $annonce): Annonce
    {
        $annonce->update([
            'publie_le' => $annonce->publie_le ?? now(),
            'actif' => true,
        ]);

        return $annonce->fresh();
    }
}
