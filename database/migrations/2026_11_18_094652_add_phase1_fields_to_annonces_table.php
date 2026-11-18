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
        Schema::table('annonces', function (Blueprint $table) {
            $table->string('categorie')->default('administration')->after('contenu');
            $table->timestamp('expire_le')->nullable()->after('publie_le');
            $table->timestamp('archivee_le')->nullable()->after('expire_le');
            $table->string('responsable', 100)->nullable()->after('archivee_le');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('annonces', function (Blueprint $table) {
            $table->dropColumn(['categorie', 'expire_le', 'archivee_le', 'responsable']);
        });
    }
};
