<?php

use App\Support\Permission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('actif')->default(true);
            $table->boolean('must_change_password')->default(false);
            $table->json('permissions')->nullable();
            $table->timestamp('last_login_at')->nullable();
        });

        DB::table('users')
            ->where('role', 'admin')
            ->update(['permissions' => json_encode(Permission::values())]);

        DB::table('users')
            ->where('role', 'secretariat')
            ->update(['permissions' => json_encode(Permission::defaultForSecretariat())]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'actif',
                'must_change_password',
                'permissions',
                'last_login_at',
            ]);
        });
    }
};
