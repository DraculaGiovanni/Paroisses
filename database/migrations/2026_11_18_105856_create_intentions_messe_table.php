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
        Schema::create('intentions_messe', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paroisse_id')->constrained()->cascadeOnDelete();
            $table->string('code_suivi', 10)->unique();
            $table->string('nom_demandeur', 100);
            $table->string('telephone', 30)->nullable();
            $table->string('type');
            $table->string('intention', 300);
            $table->date('date_souhaitee')->nullable();
            $table->foreignId('horaire_id')->nullable()->constrained()->nullOnDelete();
            $table->string('statut')->default('recue');
            $table->date('date_programmee')->nullable();
            $table->foreignId('horaire_programme_id')->nullable()->constrained('horaires')->nullOnDelete();
            $table->string('motif_refus', 200)->nullable();
            $table->foreignId('traitee_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['paroisse_id', 'statut', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intentions_messe');
    }
};
