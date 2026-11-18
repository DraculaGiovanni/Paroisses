<?php

namespace Tests\Feature\Admin;

use App\Models\Paroisse;
use App\Models\User;
use App\Support\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TeamControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_lists_only_members_of_their_paroisse_and_cannot_modify_another_paroisse_member(): void
    {
        $paroisseA = Paroisse::factory()->create();
        $paroisseB = Paroisse::factory()->create();
        $adminA = User::factory()->admin()->create(['paroisse_id' => $paroisseA->id]);
        $memberA = User::factory()->create(['paroisse_id' => $paroisseA->id]);
        $memberB = User::factory()->create(['paroisse_id' => $paroisseB->id]);

        $response = $this->actingAs($adminA)->get(route('admin.equipe.index'));
        $response->assertOk();

        $members = $response->viewData('page')['props']['members'];

        $this->assertIsArray($members);
        $this->assertContains($memberA->email, array_column($members, 'email'));
        $this->assertNotContains($memberB->email, array_column($members, 'email'));

        $this->actingAs($adminA)
            ->put(route('admin.equipe.update', $memberB), [
                'role' => 'secretariat',
                'permissions' => [],
            ])
            ->assertNotFound();

        $this->assertSame('secretariat', $memberB->fresh()->role);
    }

    public function test_super_admin_can_list_all_paroisses_and_create_a_member_for_a_selected_paroisse(): void
    {
        $paroisseA = Paroisse::factory()->create();
        $paroisseB = Paroisse::factory()->create();
        $superAdmin = User::factory()->superAdmin()->create();
        $memberA = User::factory()->create(['paroisse_id' => $paroisseA->id]);

        $response = $this->actingAs($superAdmin)->get(route('admin.equipe.index'));
        $response->assertOk();

        $members = $response->viewData('page')['props']['members'];
        $this->assertContains($memberA->email, array_column($members, 'email'));

        $this->actingAs($superAdmin)
            ->put(route('admin.equipe.update', $memberA), [
                'permissions' => [Permission::HorairesGerer->value],
            ])
            ->assertRedirect(route('admin.equipe.index'));

        $this->assertSame([Permission::HorairesGerer->value], $memberA->fresh()->permissions);

        $this->actingAs($superAdmin)
            ->post(route('admin.equipe.store'), [
                'name' => 'Membre de la paroisse B',
                'email' => 'membre-b@example.test',
                'paroisse_id' => $paroisseB->id,
                'permissions' => [Permission::HorairesGerer->value],
            ])
            ->assertRedirect(route('admin.equipe.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'membre-b@example.test',
            'paroisse_id' => $paroisseB->id,
            'role' => 'secretariat',
        ]);
    }

    public function test_secretariat_is_forbidden_from_team_and_paroisse_settings(): void
    {
        $paroisse = Paroisse::factory()->create();
        $secretariat = User::factory()->create(['paroisse_id' => $paroisse->id]);

        $this->actingAs($secretariat)
            ->get(route('admin.equipe.index'))
            ->assertForbidden();

        $this->actingAs($secretariat)
            ->get(route('admin.paroisse.edit'))
            ->assertForbidden();

        $this->actingAs($secretariat)
            ->put(route('admin.paroisse.update'), ['nom' => 'Nom interdit'])
            ->assertForbidden();
    }

    public function test_admin_creates_member_with_one_time_hashed_temporary_password(): void
    {
        $paroisse = Paroisse::factory()->create();
        $admin = User::factory()->admin()->create(['paroisse_id' => $paroisse->id]);

        $response = $this->actingAs($admin)->post(route('admin.equipe.store'), [
            'name' => 'Secrétaire créée',
            'email' => 'secretaire@example.test',
            'permissions' => [
                Permission::AnnoncesGerer->value,
                Permission::HorairesGerer->value,
            ],
        ]);

        $response->assertRedirect(route('admin.equipe.index'));
        $response->assertSessionHas('temporary_password');

        $temporaryPassword = session('temporary_password');
        $member = User::where('email', 'secretaire@example.test')->firstOrFail();

        $this->assertIsString($temporaryPassword);
        $this->assertGreaterThanOrEqual(12, strlen($temporaryPassword));
        $this->assertTrue(Hash::check($temporaryPassword, $member->password));
        $this->assertNotSame($temporaryPassword, $member->getRawOriginal('password'));
        $this->assertTrue($member->must_change_password);
        $this->assertSame([
            Permission::AnnoncesGerer->value,
            Permission::HorairesGerer->value,
        ], $member->permissions);

        $firstPage = $this->get(route('admin.equipe.index'));
        $firstPage->assertOk();
        $this->assertSame(
            $temporaryPassword,
            $firstPage->viewData('page')['props']['flash']['temporary_password']
        );

        $secondPage = $this->get(route('admin.equipe.index'));
        $secondPage->assertOk();
        $this->assertNull($secondPage->viewData('page')['props']['flash']['temporary_password']);
    }

    public function test_admin_cannot_create_duplicate_emails_or_unknown_permissions(): void
    {
        $paroisse = Paroisse::factory()->create();
        $admin = User::factory()->admin()->create(['paroisse_id' => $paroisse->id]);
        User::factory()->create(['email' => 'existing@example.test', 'paroisse_id' => $paroisse->id]);

        $this->actingAs($admin)
            ->from(route('admin.equipe.index'))
            ->post(route('admin.equipe.store'), [
                'name' => 'Duplicate',
                'email' => 'existing@example.test',
                'permissions' => [],
            ])
            ->assertSessionHasErrors('email');

        $this->actingAs($admin)
            ->from(route('admin.equipe.index'))
            ->post(route('admin.equipe.store'), [
                'name' => 'Invalid permission',
                'email' => 'invalid-permission@example.test',
                'permissions' => ['users.delete'],
            ])
            ->assertSessionHasErrors('permissions.0');

        $this->assertDatabaseMissing('users', ['email' => 'invalid-permission@example.test']);
    }

    public function test_admin_can_update_permissions_reset_password_and_toggle_member_active_state(): void
    {
        $paroisse = Paroisse::factory()->create();
        $admin = User::factory()->admin()->create(['paroisse_id' => $paroisse->id]);
        $member = User::factory()->create([
            'paroisse_id' => $paroisse->id,
            'permissions' => [Permission::AnnoncesGerer->value],
        ]);

        $this->actingAs($admin)
            ->put(route('admin.equipe.update', $member), [
                'permissions' => [Permission::HorairesGerer->value],
            ])
            ->assertRedirect(route('admin.equipe.index'));

        $this->assertSame([Permission::HorairesGerer->value], $member->fresh()->permissions);

        $resetResponse = $this->actingAs($admin)
            ->post(route('admin.equipe.reset-password', $member));
        $resetResponse->assertRedirect(route('admin.equipe.index'));
        $resetResponse->assertSessionHas('temporary_password');

        $temporaryPassword = session('temporary_password');
        $member->refresh();
        $this->assertTrue(Hash::check($temporaryPassword, $member->password));
        $this->assertTrue($member->must_change_password);

        $this->actingAs($admin)
            ->patch(route('admin.equipe.toggle-actif', $member))
            ->assertRedirect(route('admin.equipe.index'));

        $this->assertFalse($member->fresh()->actif);

        $this->actingAs($admin)
            ->patch(route('admin.equipe.toggle-actif', $member))
            ->assertRedirect(route('admin.equipe.index'));

        $this->assertTrue($member->fresh()->actif);
        $this->assertModelExists($member);
    }

    public function test_admin_cannot_disable_or_demote_themselves_or_the_last_active_admin(): void
    {
        $paroisse = Paroisse::factory()->create();
        $admin = User::factory()->admin()->create(['paroisse_id' => $paroisse->id]);
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->from(route('admin.equipe.index'))
            ->patch(route('admin.equipe.toggle-actif', $admin))
            ->assertSessionHasErrors('actif');

        $this->actingAs($admin)
            ->from(route('admin.equipe.index'))
            ->put(route('admin.equipe.update', $admin), [
                'role' => 'secretariat',
                'permissions' => [],
            ])
            ->assertSessionHasErrors('role');

        $this->actingAs($superAdmin)
            ->from(route('admin.equipe.index'))
            ->patch(route('admin.equipe.toggle-actif', $admin))
            ->assertSessionHasErrors('actif');

        $this->assertTrue($admin->fresh()->actif);
        $this->assertSame('admin', $admin->fresh()->role);
    }

    public function test_secretariat_permissions_are_enforced_by_announcement_and_schedule_policies(): void
    {
        $paroisse = Paroisse::factory()->create();
        $secretariat = User::factory()->create([
            'paroisse_id' => $paroisse->id,
            'permissions' => [Permission::AnnoncesGerer->value],
        ]);

        $this->actingAs($secretariat)
            ->get(route('admin.annonces.index'))
            ->assertOk();

        $this->actingAs($secretariat)
            ->post(route('admin.annonces.store'), [
                'titre' => 'Annonce autorisée',
                'contenu' => 'Contenu autorisé',
            ])
            ->assertRedirect(route('admin.annonces.index'));

        $this->actingAs($secretariat)
            ->get(route('admin.horaires.index'))
            ->assertForbidden();

        $this->actingAs($secretariat)
            ->post(route('admin.horaires.store'), [
                'type' => 'messe',
                'jour_semaine' => 1,
                'heure' => '09:00',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('annonces', [
            'paroisse_id' => $paroisse->id,
            'titre' => 'Annonce autorisée',
        ]);
        $this->assertDatabaseMissing('horaires', ['paroisse_id' => $paroisse->id]);
    }
}
