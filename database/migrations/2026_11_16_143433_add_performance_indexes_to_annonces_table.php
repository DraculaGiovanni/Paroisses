<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Ajoute un index composite pour optimiser l'affichage des annonces épinglées.
     */
    public function up(): void
    {
        Schema::table('annonces', function (Blueprint $table) {
            // Ajoute index pour les annonces épinglées (affichées en premier)
            $table->index(['paroisse_id', 'epinglee', 'publie_le'], 'annonces_paroisse_epinglee_publie');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('annonces', function (Blueprint $table) {
            $table->dropIndex('annonces_paroisse_epinglee_publie');
        });
    }
};
