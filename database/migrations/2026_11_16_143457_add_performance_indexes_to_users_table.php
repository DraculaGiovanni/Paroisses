<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Ajoute des index pour optimiser les recherches d'utilisateurs.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Index pour filtrer par rôle (admin, secretariat, super_admin)
            $table->index('role');

            // Note: paroisse_id a déjà un index via la clé étrangère
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
        });
    }
};
