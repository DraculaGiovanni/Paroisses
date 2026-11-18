<?php

namespace Tests\Feature\Admin;

use App\Models\Paroisse;
use App\Models\User;
use App\Support\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TeamSecurityMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_security_fields_exist_with_expected_defaults(): void
    {
        $this->assertTrue(Schema::hasColumns('users', [
            'actif',
            'must_change_password',
            'permissions',
            'last_login_at',
        ]));

        $paroisse = Paroisse::factory()->create();
        $admin = User::factory()->admin()->create(['paroisse_id' => $paroisse->id]);
        $secretariat = User::factory()->create(['paroisse_id' => $paroisse->id]);

        $this->assertTrue($admin->actif);
        $this->assertFalse($admin->must_change_password);
        $this->assertSame(Permission::values(), $admin->permissions);
        $this->assertTrue($secretariat->actif);
        $this->assertFalse($secretariat->must_change_password);
        $this->assertSame([
            Permission::AnnoncesGerer->value,
            Permission::HorairesGerer->value,
            Permission::IntentionsGerer->value,
        ], $secretariat->permissions);
        $this->assertNull($admin->last_login_at);
        $this->assertNull($secretariat->last_login_at);
    }

    public function test_migration_backfills_permissions_for_existing_users_by_role(): void
    {
        $paroisse = Paroisse::factory()->create();
        $admin = User::factory()->admin()->create(['paroisse_id' => $paroisse->id]);
        $secretariat = User::factory()->create(['paroisse_id' => $paroisse->id]);
        $migration = require database_path('migrations/2026_11_17_164418_add_team_security_fields_to_users_table.php');

        $migration->down();
        $migration->up();

        $this->assertSame(Permission::values(), $admin->fresh()->permissions);
        $this->assertSame([
            Permission::AnnoncesGerer->value,
            Permission::HorairesGerer->value,
            Permission::IntentionsGerer->value,
        ], $secretariat->fresh()->permissions);
    }
}
