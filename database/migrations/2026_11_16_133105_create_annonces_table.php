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
        Schema::create('annonces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paroisse_id')->constrained()->cascadeOnDelete();
            $table->string('titre');
            $table->text('contenu');
            $table->timestamp('publie_le')->nullable();      // null = brouillon
            $table->boolean('epinglee')->default(false);
            $table->boolean('notifier')->default(false);     // servira à l'étape des notifications
            $table->timestamps();

            $table->index(['paroisse_id', 'publie_le']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('annonces');
    }
};
