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
        Schema::table('paroisses', function (Blueprint $table) {
            $table->string('couleur_principale', 7)->nullable()->after('logo_path')
                ->comment('Couleur d\'accent de la paroisse en hexadécimal (ex: #1E40AF)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paroisses', function (Blueprint $table) {
            $table->dropColumn('couleur_principale');
        });
    }
};
