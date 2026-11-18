<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Ajoute un index composite incluant 'actif' pour optimiser
     * les requêtes publiques qui filtrent les horaires actifs.
     *
     * Note: On garde l'index existant (paroisse_id, jour_semaine, heure)
     * et on ajoute un nouvel index qui inclut 'actif'.
     */
    public function up(): void
    {
        Schema::table('horaires', function (Blueprint $table) {
            // Ajoute le nouvel index composite optimal pour filtrer par actif
            $table->index(['paroisse_id', 'actif', 'jour_semaine', 'heure'], 'horaires_paroisse_actif_jour_heure');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('horaires', function (Blueprint $table) {
            $table->dropIndex('horaires_paroisse_actif_jour_heure');
        });
    }
};
