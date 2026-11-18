<?php

namespace Tests\Feature;

use App\Models\Paroisse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ParoisseColorValidationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function admin_can_update_paroisse_with_valid_dark_color()
    {
        $paroisse = Paroisse::factory()->create();
        $admin = User::factory()->admin()->create(['paroisse_id' => $paroisse->id]);

        $this->actingAs($admin)
            ->put(route('admin.paroisse.update'), [
                'nom' => $paroisse->nom,
                'couleur_principale' => '#1E40AF',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('paroisses', [
            'id' => $paroisse->id,
            'couleur_principale' => '#1E40AF',
        ]);
    }

    #[Test]
    public function admin_cannot_update_paroisse_with_light_color()
    {
        $paroisse = Paroisse::factory()->create();
        $admin = User::factory()->admin()->create(['paroisse_id' => $paroisse->id]);

        $this->actingAs($admin)
            ->put(route('admin.paroisse.update'), [
                'nom' => $paroisse->nom,
                'couleur_principale' => '#FFD700', // or clair
            ])
            ->assertSessionHasErrors('couleur_principale');
    }

    #[Test]
    public function admin_cannot_update_paroisse_with_invalid_format()
    {
        $paroisse = Paroisse::factory()->create();
        $admin = User::factory()->admin()->create(['paroisse_id' => $paroisse->id]);

        // Format invalide : nom de couleur
        $this->actingAs($admin)
            ->put(route('admin.paroisse.update'), [
                'nom' => $paroisse->nom,
                'couleur_principale' => 'red',
            ])
            ->assertSessionHasErrors('couleur_principale');

        // Format invalide : hex court
        $this->actingAs($admin)
            ->put(route('admin.paroisse.update'), [
                'nom' => $paroisse->nom,
                'couleur_principale' => '#FFF',
            ])
            ->assertSessionHasErrors('couleur_principale');

        // Format invalide : caractères invalides
        $this->actingAs($admin)
            ->put(route('admin.paroisse.update'), [
                'nom' => $paroisse->nom,
                'couleur_principale' => '#GGGGGG',
            ])
            ->assertSessionHasErrors('couleur_principale');

        // Format invalide : longueur incorrecte
        $this->actingAs($admin)
            ->put(route('admin.paroisse.update'), [
                'nom' => $paroisse->nom,
                'couleur_principale' => '#12345',
            ])
            ->assertSessionHasErrors('couleur_principale');

        // Injection : CSS
        $this->actingAs($admin)
            ->put(route('admin.paroisse.update'), [
                'nom' => $paroisse->nom,
                'couleur_principale' => '#1E40AF;background:url(x)',
            ])
            ->assertSessionHasErrors('couleur_principale');

        // Injection : JavaScript
        $this->actingAs($admin)
            ->put(route('admin.paroisse.update'), [
                'nom' => $paroisse->nom,
                'couleur_principale' => 'javascript:alert(1)',
            ])
            ->assertSessionHasErrors('couleur_principale');
    }

    #[Test]
    public function super_admin_can_create_paroisse_with_valid_color()
    {
        $super = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($super)
            ->post(route('super.paroisses.store'), [
                'nom' => 'Nouvelle Paroisse',
                'diocese' => 'Porto-Novo',
                'couleur_principale' => '#047857',
                'admin_name' => 'Admin Test',
                'admin_email' => 'admin@test.com',
                'admin_password' => 'password123',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('super.paroisses.index'));

        $paroisse = Paroisse::query()
            ->where('nom', 'Nouvelle Paroisse')
            ->first();

        $this->assertNotNull($paroisse);
        $this->assertEquals('#047857', $paroisse->couleur_principale);
    }

    #[Test]
    public function super_admin_cannot_create_paroisse_with_light_color()
    {
        $super = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($super)
            ->post(route('super.paroisses.store'), [
                'nom' => 'Nouvelle Paroisse',
                'couleur_principale' => '#FFAAAA', // rose clair
                'admin_name' => 'Admin Test',
                'admin_email' => 'admin@test.com',
                'admin_password' => 'password123',
            ])
            ->assertSessionHasErrors('couleur_principale');

        // Ne doit pas être créée
        $paroisse = Paroisse::query()
            ->where('nom', 'Nouvelle Paroisse')
            ->first();

        $this->assertNull($paroisse);
    }

    #[Test]
    public function super_admin_cannot_create_paroisse_with_invalid_color_format()
    {
        $super = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($super)
            ->post(route('super.paroisses.store'), [
                'nom' => 'Nouvelle Paroisse',
                'couleur_principale' => '#FFF', // trop court
                'admin_name' => 'Admin Test',
                'admin_email' => 'admin@test.com',
                'admin_password' => 'password123',
            ])
            ->assertSessionHasErrors('couleur_principale');
    }

    #[Test]
    public function color_is_optional_for_both_create_and_update()
    {
        // Update sans couleur
        $paroisse = Paroisse::factory()->create();
        $admin = User::factory()->admin()->create(['paroisse_id' => $paroisse->id]);

        $this->actingAs($admin)
            ->put(route('admin.paroisse.update'), [
                'nom' => $paroisse->nom,
                'couleur_principale' => null,
            ])
            ->assertSessionHasNoErrors();

        // Create sans couleur
        $super = User::factory()->create(['role' => 'super']);

        $this->actingAs($super)
            ->post(route('super.paroisses.store'), [
                'nom' => 'Paroisse Sans Couleur',
                'admin_name' => 'Admin Test',
                'admin_email' => 'admin2@test.com',
                'admin_password' => 'password123',
            ])
            ->assertSessionHasNoErrors();
    }

    #[Test]
    public function only_admin_can_update_their_paroisse_color()
    {
        $paroisse1 = Paroisse::factory()->create();
        $paroisse2 = Paroisse::factory()->create();
        $admin1 = User::factory()->admin()->create(['paroisse_id' => $paroisse1->id]);

        // Admin1 ne peut pas modifier paroisse2
        $this->actingAs($admin1)
            ->put(route('admin.paroisse.update'), [
                'nom' => $paroisse1->nom,
                'couleur_principale' => '#1E40AF',
            ])
            ->assertSessionHasNoErrors();

        // Vérifie que seule sa paroisse est modifiée
        $this->assertDatabaseHas('paroisses', [
            'id' => $paroisse1->id,
            'couleur_principale' => '#1E40AF',
        ]);

        $this->assertDatabaseMissing('paroisses', [
            'id' => $paroisse2->id,
            'couleur_principale' => '#1E40AF',
        ]);
    }

    #[Test]
    public function only_super_admin_can_create_paroisses()
    {
        $admin = User::factory()->admin()->create(['paroisse_id' => Paroisse::factory()->create()->id]);

        $this->actingAs($admin)
            ->post(route('super.paroisses.store'), [
                'nom' => 'Nouvelle Paroisse',
                'admin_name' => 'Admin Test',
                'admin_email' => 'admin@test.com',
                'admin_password' => 'password123',
            ])
            ->assertForbidden();
    }
}
