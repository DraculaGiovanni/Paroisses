<?php

namespace Tests\Feature;

use App\Models\Annonce;
use App\Models\Horaire;
use App\Models\Paroisse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PolicyAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected Paroisse $paroisse;

    protected User $superAdmin;

    protected User $admin;

    protected User $secretariat;

    protected User $guest;

    protected Horaire $horaire;

    protected Annonce $annonce;

    protected function setUp(): void
    {
        parent::setUp();

        $this->paroisse = Paroisse::factory()->create();

        // Créer des utilisateurs avec différents rôles
        $this->superAdmin = User::factory()->create([
            'paroisse_id' => null, // Super admin n'appartient à aucune paroisse
            'role' => 'super_admin',
            'email' => 'super@example.com',
        ]);

        $this->admin = User::factory()->create([
            'paroisse_id' => $this->paroisse->id,
            'role' => 'admin',
            'email' => 'admin@example.com',
        ]);

        $this->secretariat = User::factory()->create([
            'paroisse_id' => $this->paroisse->id,
            'role' => 'secretariat',
            'email' => 'secretariat@example.com',
        ]);

        $this->guest = User::factory()->create([
            'paroisse_id' => $this->paroisse->id,
            'role' => 'guest',
            'email' => 'guest@example.com',
        ]);

        $this->horaire = Horaire::factory()->create(['paroisse_id' => $this->paroisse->id]);
        $this->annonce = Annonce::factory()->create(['paroisse_id' => $this->paroisse->id]);
    }

    // ========== ANNONCE POLICY TESTS ==========

    public function test_admin_can_view_any_annonces()
    {
        $this->assertTrue($this->admin->can('viewAny', Annonce::class));
    }

    public function test_secretariat_can_view_any_annonces()
    {
        $this->assertTrue($this->secretariat->can('viewAny', Annonce::class));
    }

    public function test_guest_cannot_view_any_annonces()
    {
        $this->assertFalse($this->guest->can('viewAny', Annonce::class));
    }

    public function test_admin_can_create_annonces()
    {
        $this->assertTrue($this->admin->can('create', Annonce::class));
    }

    public function test_secretariat_can_create_annonces()
    {
        $this->assertTrue($this->secretariat->can('create', Annonce::class));
    }

    public function test_guest_cannot_create_annonces()
    {
        $this->assertFalse($this->guest->can('create', Annonce::class));
    }

    public function test_admin_can_update_annonces_in_their_paroisse()
    {
        $this->assertTrue($this->admin->can('update', $this->annonce));
    }

    public function test_secretariat_can_update_annonces_in_their_paroisse()
    {
        $this->assertTrue($this->secretariat->can('update', $this->annonce));
    }

    public function test_guest_cannot_update_annonces()
    {
        $this->assertFalse($this->guest->can('update', $this->annonce));
    }

    public function test_admin_can_delete_annonces()
    {
        $this->assertTrue($this->admin->can('delete', $this->annonce));
    }

    public function test_secretariat_cannot_delete_annonces()
    {
        $this->assertFalse($this->secretariat->can('delete', $this->annonce));
    }

    public function test_guest_cannot_delete_annonces()
    {
        $this->assertFalse($this->guest->can('delete', $this->annonce));
    }

    // ========== HORAIRE POLICY TESTS ==========

    public function test_admin_can_view_any_horaires()
    {
        $this->assertTrue($this->admin->can('viewAny', Horaire::class));
    }

    public function test_secretariat_can_view_any_horaires()
    {
        $this->assertTrue($this->secretariat->can('viewAny', Horaire::class));
    }

    public function test_guest_cannot_view_any_horaires()
    {
        $this->assertFalse($this->guest->can('viewAny', Horaire::class));
    }

    public function test_admin_can_create_horaires()
    {
        $this->assertTrue($this->admin->can('create', Horaire::class));
    }

    public function test_secretariat_can_create_horaires()
    {
        $this->assertTrue($this->secretariat->can('create', Horaire::class));
    }

    public function test_guest_cannot_create_horaires()
    {
        $this->assertFalse($this->guest->can('create', Horaire::class));
    }

    public function test_admin_can_update_horaires_in_their_paroisse()
    {
        $this->assertTrue($this->admin->can('update', $this->horaire));
    }

    public function test_secretariat_can_update_horaires_in_their_paroisse()
    {
        $this->assertTrue($this->secretariat->can('update', $this->horaire));
    }

    public function test_guest_cannot_update_horaires()
    {
        $this->assertFalse($this->guest->can('update', $this->horaire));
    }

    public function test_admin_can_delete_horaires()
    {
        $this->assertTrue($this->admin->can('delete', $this->horaire));
    }

    public function test_secretariat_cannot_delete_horaires()
    {
        $this->assertFalse($this->secretariat->can('delete', $this->horaire));
    }

    public function test_guest_cannot_delete_horaires()
    {
        $this->assertFalse($this->guest->can('delete', $this->horaire));
    }

    // ========== PAROISSE POLICY TESTS ==========

    public function test_super_admin_can_view_any_paroisses()
    {
        $this->assertTrue($this->superAdmin->can('viewAny', Paroisse::class));
    }

    public function test_admin_cannot_view_any_paroisses()
    {
        $this->assertFalse($this->admin->can('viewAny', Paroisse::class));
    }

    public function test_super_admin_can_create_paroisses()
    {
        $this->assertTrue($this->superAdmin->can('create', Paroisse::class));
    }

    public function test_admin_cannot_create_paroisses()
    {
        $this->assertFalse($this->admin->can('create', Paroisse::class));
    }

    public function test_super_admin_can_update_any_paroisse()
    {
        $this->assertTrue($this->superAdmin->can('update', $this->paroisse));
    }

    public function test_admin_can_update_their_own_paroisse()
    {
        $this->assertTrue($this->admin->can('update', $this->paroisse));
    }

    public function test_secretariat_cannot_update_paroisse()
    {
        $this->assertFalse($this->secretariat->can('update', $this->paroisse));
    }

    public function test_super_admin_can_delete_paroisses()
    {
        $this->assertTrue($this->superAdmin->can('delete', $this->paroisse));
    }

    public function test_admin_cannot_delete_paroisses()
    {
        $this->assertFalse($this->admin->can('delete', $this->paroisse));
    }

    public function test_admin_can_manage_users_in_their_paroisse()
    {
        $this->assertTrue($this->admin->can('manageUsers', $this->paroisse));
    }

    public function test_secretariat_cannot_manage_users()
    {
        $this->assertFalse($this->secretariat->can('manageUsers', $this->paroisse));
    }

    // ========== ROLE HIERARCHY TESTS ==========

    public function test_super_admin_has_highest_privileges()
    {
        // Super admin peut créer des paroisses
        $this->assertTrue($this->superAdmin->can('create', Paroisse::class));

        // Super admin peut voir toutes les paroisses
        $this->assertTrue($this->superAdmin->can('viewAny', Paroisse::class));

        // Super admin peut supprimer des paroisses
        $this->assertTrue($this->superAdmin->can('delete', $this->paroisse));
    }

    public function test_admin_has_full_control_over_their_paroisse_content()
    {
        // Admin peut créer, modifier et supprimer annonces et horaires
        $this->assertTrue($this->admin->can('create', Annonce::class));
        $this->assertTrue($this->admin->can('update', $this->annonce));
        $this->assertTrue($this->admin->can('delete', $this->annonce));

        $this->assertTrue($this->admin->can('create', Horaire::class));
        $this->assertTrue($this->admin->can('update', $this->horaire));
        $this->assertTrue($this->admin->can('delete', $this->horaire));

        // Admin peut modifier les paramètres de sa paroisse
        $this->assertTrue($this->admin->can('update', $this->paroisse));
    }

    public function test_secretariat_can_create_and_edit_but_not_delete()
    {
        // Secrétariat peut créer et modifier
        $this->assertTrue($this->secretariat->can('create', Annonce::class));
        $this->assertTrue($this->secretariat->can('update', $this->annonce));

        $this->assertTrue($this->secretariat->can('create', Horaire::class));
        $this->assertTrue($this->secretariat->can('update', $this->horaire));

        // Mais ne peut pas supprimer
        $this->assertFalse($this->secretariat->can('delete', $this->annonce));
        $this->assertFalse($this->secretariat->can('delete', $this->horaire));

        // Ne peut pas modifier les paramètres de la paroisse
        $this->assertFalse($this->secretariat->can('update', $this->paroisse));
    }

    public function test_guest_has_no_admin_privileges()
    {
        // Guest ne peut rien faire dans l'admin
        $this->assertFalse($this->guest->can('viewAny', Annonce::class));
        $this->assertFalse($this->guest->can('create', Annonce::class));
        $this->assertFalse($this->guest->can('update', $this->annonce));
        $this->assertFalse($this->guest->can('delete', $this->annonce));

        $this->assertFalse($this->guest->can('viewAny', Horaire::class));
        $this->assertFalse($this->guest->can('create', Horaire::class));
        $this->assertFalse($this->guest->can('update', $this->horaire));
        $this->assertFalse($this->guest->can('delete', $this->horaire));

        $this->assertFalse($this->guest->can('update', $this->paroisse));
    }

    public function test_guest_cannot_access_admin_announcement_or_horaire_routes(): void
    {
        $this->actingAs($this->guest)
            ->get(route('admin.annonces.index'))
            ->assertForbidden();

        $this->actingAs($this->guest)
            ->post(route('admin.annonces.store'), [
                'titre' => 'Annonce test',
                'contenu' => 'Contenu test',
            ])
            ->assertForbidden();

        $this->actingAs($this->guest)
            ->get(route('admin.horaires.index'))
            ->assertForbidden();

        $this->actingAs($this->guest)
            ->post(route('admin.horaires.store'), [
                'type' => 'messe',
                'jour_semaine' => 1,
                'heure' => '09:00',
            ])
            ->assertForbidden();
    }

    public function test_secretariat_cannot_delete_announcements_or_horaires_through_admin_routes(): void
    {
        $this->actingAs($this->secretariat)
            ->delete(route('admin.annonces.destroy', $this->annonce))
            ->assertForbidden();

        $this->actingAs($this->secretariat)
            ->delete(route('admin.horaires.destroy', $this->horaire))
            ->assertForbidden();

        $this->assertModelExists($this->annonce);
        $this->assertModelExists($this->horaire);
    }

    public function test_user_without_paroisse_cannot_access_admin_features()
    {
        $userWithoutParoisse = User::factory()->create([
            'paroisse_id' => null,
            'role' => 'admin',
        ]);

        // Ne peut pas voir les annonces/horaires sans paroisse
        $this->assertFalse($userWithoutParoisse->can('viewAny', Annonce::class));
        $this->assertFalse($userWithoutParoisse->can('viewAny', Horaire::class));

        // Ne peut pas créer de contenu
        $this->assertFalse($userWithoutParoisse->can('create', Annonce::class));
        $this->assertFalse($userWithoutParoisse->can('create', Horaire::class));
    }

    // ========== CROSS-PAROISSE AUTHORIZATION TESTS ==========

    public function test_admin_cannot_view_resources_from_other_paroisse()
    {
        $otherParoisse = Paroisse::factory()->create();
        $otherHoraire = Horaire::factory()->create(['paroisse_id' => $otherParoisse->id]);
        $otherAnnonce = Annonce::factory()->create(['paroisse_id' => $otherParoisse->id]);

        $this->assertFalse($this->admin->can('view', $otherHoraire));
        $this->assertFalse($this->admin->can('view', $otherAnnonce));
    }

    public function test_admin_cannot_update_resources_from_other_paroisse()
    {
        $otherParoisse = Paroisse::factory()->create();
        $otherHoraire = Horaire::factory()->create(['paroisse_id' => $otherParoisse->id]);
        $otherAnnonce = Annonce::factory()->create(['paroisse_id' => $otherParoisse->id]);

        $this->assertFalse($this->admin->can('update', $otherHoraire));
        $this->assertFalse($this->admin->can('update', $otherAnnonce));
        $this->assertFalse($this->admin->can('update', $otherParoisse));
    }

    public function test_admin_cannot_delete_resources_from_other_paroisse()
    {
        $otherParoisse = Paroisse::factory()->create();
        $otherHoraire = Horaire::factory()->create(['paroisse_id' => $otherParoisse->id]);
        $otherAnnonce = Annonce::factory()->create(['paroisse_id' => $otherParoisse->id]);

        $this->assertFalse($this->admin->can('delete', $otherHoraire));
        $this->assertFalse($this->admin->can('delete', $otherAnnonce));
    }

    public function test_super_admin_can_access_all_paroisses()
    {
        $otherParoisse = Paroisse::factory()->create();

        $this->assertTrue($this->superAdmin->can('view', $this->paroisse));
        $this->assertTrue($this->superAdmin->can('view', $otherParoisse));
        $this->assertTrue($this->superAdmin->can('update', $this->paroisse));
        $this->assertTrue($this->superAdmin->can('update', $otherParoisse));
    }
}
