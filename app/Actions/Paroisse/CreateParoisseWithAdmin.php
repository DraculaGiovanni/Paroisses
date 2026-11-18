<?php

namespace App\Actions\Paroisse;

use App\Models\Paroisse;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateParoisseWithAdmin
{
    /**
     * Crée une nouvelle paroisse avec son administrateur.
     *
     * Utilise une transaction pour garantir que si la création
     * de l'admin échoue, la paroisse n'est pas créée non plus.
     *
     * @param  array  $data  Données validées du formulaire
     * @return Paroisse La paroisse créée
     */
    public function execute(array $data): Paroisse
    {
        return DB::transaction(function () use ($data) {
            // 1. Créer la paroisse
            $paroisse = Paroisse::create([
                'nom' => $data['nom'],
                'slug' => Paroisse::uniqueSlug($data['nom']),
                'diocese' => $data['diocese'] ?? null,
            ]);

            // 2. Créer l'administrateur de la paroisse
            User::create([
                'name' => $data['admin_name'],
                'email' => $data['admin_email'],
                'password' => Hash::make($data['admin_password']),
                'paroisse_id' => $paroisse->id,
                'role' => 'admin',
                'email_verified_at' => now(), // Auto-vérifié pour les admins créés par super admin
            ]);

            return $paroisse->fresh();
        });
    }
}
