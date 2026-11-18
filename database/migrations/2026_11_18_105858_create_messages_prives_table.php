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
        Schema::create('messages_prives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paroisse_id')->constrained()->cascadeOnDelete();
            $table->string('sujet');
            $table->text('message');
            $table->string('nom', 100)->nullable();
            $table->string('contact', 100)->nullable();
            $table->boolean('souhaite_reponse')->default(false);
            $table->string('statut')->default('nouveau');
            $table->foreignId('lu_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['paroisse_id', 'statut']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages_prives');
    }
};
