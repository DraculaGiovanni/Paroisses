<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('horaires', function (Blueprint $table) {
            $table->time('heure_fin')->nullable()->after('heure');
            $table->date('valide_du')->nullable()->after('heure_fin');
            $table->date('valide_au')->nullable()->after('valide_du');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('horaires', function (Blueprint $table) {
            $table->dropColumn(['heure_fin', 'valide_du', 'valide_au']);
        });
    }
};
