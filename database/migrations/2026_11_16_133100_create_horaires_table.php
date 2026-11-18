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
        Schema::create('horaires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paroisse_id')->constrained()->cascadeOnDelete();
            $table->string('type');                          // messe, confession, adoration, autre
            $table->unsignedTinyInteger('jour_semaine');     // 1 = lundi ... 7 = dimanche
            $table->time('heure');
            $table->string('lieu')->nullable();              // église principale, chapelle, etc.
            $table->string('note')->nullable();              // ex. « en langue fon »
            $table->boolean('actif')->default(true);
            $table->timestamps();

            $table->index(['paroisse_id', 'jour_semaine', 'heure']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horaires');
    }
};
