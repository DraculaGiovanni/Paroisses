<?php

use App\Support\Permission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (DB::table('users')->where('role', 'secretariat')->get(['id', 'permissions']) as $user) {
            $permissions = is_string($user->permissions) ? json_decode($user->permissions, true) : [];
            $permissions = is_array($permissions) ? $permissions : [];

            if (! in_array(Permission::IntentionsGerer->value, $permissions, true)) {
                $permissions[] = Permission::IntentionsGerer->value;
            }

            DB::table('users')->where('id', $user->id)->update([
                'permissions' => json_encode(array_values(array_unique($permissions))),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Migration de données non réversible : on ne sait pas quels secrétaires avaient déjà intentions.gerer avant son exécution. Ne rien faire évite de retirer la permission à tort.
    }
};
