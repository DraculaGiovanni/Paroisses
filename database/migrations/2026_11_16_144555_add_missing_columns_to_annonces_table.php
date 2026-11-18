<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Ajoute les colonnes date_debut, date_fin et actif à la table annonces.
     */
    public function up(): void
    {
        Schema::table('annonces', function (Blueprint $table) {
            $table->date('date_debut')->nullable()->after('contenu');
            $table->date('date_fin')->nullable()->after('date_debut');
            $table->boolean('actif')->default(true)->after('notifier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('annonces', function (Blueprint $table) {
            $table->dropColumn(['date_debut', 'date_fin', 'actif']);
        });
    }
};
