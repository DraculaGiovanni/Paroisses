<?php

namespace Tests\Feature;

use App\Models\Annonce;
use App\Models\Horaire;
use App\Models\Paroisse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParoisseIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected Paroisse $paroisseA;

    protected Paroisse $paroisseB;

    protected User $adminA;

    protected User $adminB;

    protected Horaire $horaireA;

    protected Horaire $horaireB;

    protected Annonce $annonceA;

    protected Annonce $annonceB;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer deux paroisses avec leurs admins
        $this->paroisseA = Paroisse::factory()->create(['nom' => 'Paroisse A', 'slug' => 'paroisse-a']);
        $this->paroisseB = Paroisse::factory()->create(['nom' => 'Paroisse B', 'slug' => 'paroisse-b']);

        $this->adminA = User::factory()->create([
            'paroisse_id' => $this->paroisseA->id,
            'role' => 'admin',
            'email' => 'admin.a@example.com',
        ]);

        $this->adminB = User::factory()->create([
            'paroisse_id' => $this->paroisseB->id,
            'role' => 'admin',
            'email' => 'admin.b@example.com',
        ]);

        // Créer des ressources pour chaque paroisse
        $this->horaireA = Horaire::factory()->create(['paroisse_id' => $this->paroisseA->id]);
        $this->horaireB = Horaire::factory()->create(['paroisse_id' => $this->paroisseB->id]);

        $this->annonceA = Annonce::factory()->create(['paroisse_id' => $this->paroisseA->id]);
        $this->annonceB = Annonce::factory()->create(['paroisse_id' => $this->paroisseB->id]);
    }

    public function test_admin_cannot_view_annonces_from_other_paroisse()
    {
        $response = $this->actingAs($this->adminA)
            ->get(route('admin.annonces.edit', $this->annonceB));

        $response->assertStatus(404);
    }

    public function test_admin_cannot_update_annonce_from_other_paroisse()
    {
        $response = $this->actingAs($this->adminA)
            ->put(route('admin.annonces.update', $this->annonceB), [
                'titre' => 'Titre modifié',
                'contenu' => 'Contenu modifié',
            ]);

        $response->assertStatus(404);

        // Vérifier que l'annonce n'a pas été modifiée
        $this->annonceB->refresh();
        $this->assertNotEquals('Titre modifié', $this->annonceB->titre);
    }

    public function test_admin_cannot_delete_annonce_from_other_paroisse()
    {
        $response = $this->actingAs($this->adminA)
            ->delete(route('admin.annonces.destroy', $this->annonceB));

        $response->assertStatus(404);

        // Vérifier que l'annonce existe toujours
        $this->assertDatabaseHas('annonces', ['id' => $this->annonceB->id]);
    }

    public function test_admin_cannot_view_horaires_from_other_paroisse()
    {
        // Les horaires n'ont pas de route show, mais testons via index
        $response = $this->actingAs($this->adminA)
            ->get(route('admin.horaires.index'));

        $response->assertSuccessful();

        // Vérifier que seuls les horaires de la paroisse A sont retournés via Inertia props
        $horaires = $response->viewData('page')['props']['horaires'];

        $this->assertTrue(
            collect($horaires)->contains('id', $this->horaireA->id),
            'Les horaires de la paroisse A doivent être visibles'
        );

        $this->assertFalse(
            collect($horaires)->contains('id', $this->horaireB->id),
            'Les horaires de la paroisse B ne doivent pas être visibles'
        );
    }

    public function test_admin_cannot_update_horaire_from_other_paroisse()
    {
        $this->horaireB->update(['heure' => '08:00']);

        $response = $this->actingAs($this->adminA)
            ->put(route('admin.horaires.update', $this->horaireB), [
                'type' => 'messe',
                'jour_semaine' => 1,
                'heure' => '09:00',
                'actif' => true,
            ]);

        $response->assertStatus(404);

        // Vérifier que l'horaire n'a pas été modifié
        $this->horaireB->refresh();
        $this->assertSame('08:00', $this->horaireB->heure->format('H:i'));
    }

    public function test_admin_cannot_delete_horaire_from_other_paroisse()
    {
        $response = $this->actingAs($this->adminA)
            ->delete(route('admin.horaires.destroy', $this->horaireB));

        $response->assertStatus(404);

        // Vérifier que l'horaire existe toujours
        $this->assertDatabaseHas('horaires', ['id' => $this->horaireB->id]);
    }

    public function test_admin_can_only_see_their_own_paroisse_annonces_in_index()
    {
        $response = $this->actingAs($this->adminA)
            ->get(route('admin.annonces.index'));

        $response->assertSuccessful();

        // Vérifier que seules les annonces de la paroisse A sont retournées via Inertia props
        $annonces = $response->viewData('page')['props']['annonces']['data']
            ?? $response->viewData('page')['props']['annonces'];

        $this->assertTrue(
            collect($annonces)->contains('id', $this->annonceA->id),
            'Les annonces de la paroisse A doivent être visibles'
        );

        $this->assertFalse(
            collect($annonces)->contains('id', $this->annonceB->id),
            'Les annonces de la paroisse B ne doivent pas être visibles'
        );
    }

    public function test_admin_can_edit_their_own_paroisse_resources()
    {
        // Test annonce
        $response = $this->actingAs($this->adminA)
            ->get(route('admin.annonces.edit', $this->annonceA));

        $response->assertSuccessful();

        // Test update annonce
        $response = $this->actingAs($this->adminA)
            ->put(route('admin.annonces.update', $this->annonceA), [
                'titre' => 'Nouveau titre',
                'contenu' => 'Nouveau contenu',
                'publier' => true,
            ]);

        $response->assertRedirect(route('admin.annonces.index'));

        $this->annonceA->refresh();
        $this->assertEquals('Nouveau titre', $this->annonceA->titre);
    }

    public function test_global_scope_automatically_filters_by_paroisse()
    {
        // Se connecter en tant qu'admin de la paroisse A
        $this->actingAs($this->adminA);

        // Récupérer toutes les annonces (le global scope doit filtrer automatiquement)
        $annonces = Annonce::all();

        // Doit contenir seulement les annonces de la paroisse A
        $this->assertCount(1, $annonces);
        $this->assertEquals($this->annonceA->id, $annonces->first()->id);

        // Vérifier pour les horaires aussi
        $horaires = Horaire::all();

        $this->assertCount(1, $horaires);
        $this->assertEquals($this->horaireA->id, $horaires->first()->id);
    }

    public function test_user_without_paroisse_cannot_access_admin_routes()
    {
        $userWithoutParoisse = User::factory()->create([
            'paroisse_id' => null,
            'role' => 'admin',
        ]);

        $response = $this->actingAs($userWithoutParoisse)
            ->get(route('admin.annonces.index'));

        // Le middleware paroisse.isolation doit bloquer l'accès (403 ou redirect)
        $this->assertTrue(
            in_array($response->status(), [403, 302, 301]),
            "L'accès doit être bloqué pour un utilisateur sans paroisse (status: {$response->status()})"
        );
    }

    public function test_super_admin_without_paroisse_can_access_admin_routes()
    {
        $superAdmin = User::factory()->create([
            'paroisse_id' => null,
            'role' => 'super_admin',
        ]);

        $response = $this->actingAs($superAdmin)
            ->get(route('admin.annonces.index'));

        // Le super admin peut accéder aux routes admin même sans paroisse
        // (mais ne verra pas de contenu car le global scope filtre par paroisse_id)
        $response->assertSuccessful();
    }

    public function test_attempting_to_create_resource_for_another_paroisse_fails()
    {
        // Se connecter en tant qu'admin de la paroisse A
        $this->actingAs($this->adminA);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('ne correspond pas à celle de l\'utilisateur');

        Annonce::create([
            'titre' => 'Test',
            'contenu' => 'Contenu test',
            'paroisse_id' => $this->paroisseB->id,
        ]);
    }

    public function test_admin_creation_without_paroisse_id_uses_their_own_paroisse()
    {
        $this->actingAs($this->adminA);

        $annonce = Annonce::create([
            'titre' => 'Annonce de la paroisse A',
            'contenu' => 'Contenu test',
        ]);

        $this->assertEquals($this->paroisseA->id, $annonce->paroisse_id);
    }

    public function test_public_page_reads_only_the_requested_paroisse_when_another_admin_is_signed_in()
    {
        $this->annonceA->update([
            'publie_le' => now()->subDay(),
            'actif' => true,
        ]);
        $this->annonceB->update([
            'publie_le' => now()->subDay(),
            'actif' => true,
        ]);

        $this->actingAs($this->adminA);

        $response = $this->get(route('paroisse.show', $this->paroisseB));

        $response->assertSuccessful();
        $annonces = $response->viewData('page')['props']['annonces'];

        $this->assertIsArray($annonces);
        $this->assertTrue(collect($annonces)->contains('id', $this->annonceB->id));
        $this->assertFalse(collect($annonces)->contains('id', $this->annonceA->id));
    }
}
