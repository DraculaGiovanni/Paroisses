<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paroisses', function (Blueprint $table): void {
            $table->string('banniere_path')->nullable()->after('logo_path');
        });
    }

    public function down(): void
    {
        Schema::table('paroisses', function (Blueprint $table): void {
            $table->dropColumn('banniere_path');
        });
    }
};
