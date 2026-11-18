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
        foreach (DB::table('users')->where('role', 'secretariat')->get(['id', 'permissions']) as $user) {
            $permissions = is_string($user->permissions) ? json_decode($user->permissions, true) : [];
            $permissions = is_array($permissions) ? $permissions : [];
            $permissions = array_values(array_filter(
                $permissions,
                static fn (string $permission): bool => $permission !== Permission::IntentionsGerer->value
            ));

            DB::table('users')->where('id', $user->id)->update([
                'permissions' => json_encode($permissions),
            ]);
        }
    }
};
