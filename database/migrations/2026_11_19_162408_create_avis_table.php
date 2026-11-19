<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avis', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('paroisse_id')->constrained()->cascadeOnDelete();
            $table->text('message');
            $table->string('statut', 20)->default('en_attente');
            $table->timestamp('modere_le')->nullable();
            $table->timestamps();

            $table->index(['paroisse_id', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avis');
    }
};
