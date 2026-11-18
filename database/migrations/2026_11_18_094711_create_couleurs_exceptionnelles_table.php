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
        Schema::create('couleurs_exceptionnelles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paroisse_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('couleur', 20);
            $table->string('libelle', 150)->nullable();
            $table->timestamps();
            $table->unique(['paroisse_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('couleurs_exceptionnelles');
    }
};
