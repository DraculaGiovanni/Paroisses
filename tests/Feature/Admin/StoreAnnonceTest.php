<?php

namespace Tests\Feature\Admin;

use App\Models\Annonce;
use App\Models\Paroisse;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreAnnonceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_an_annonce_with_the_form_payload(): void
    {
        $paroisse = Paroisse::factory()->create();

        $response = $this->actingAsAdmin($paroisse)
            ->post(route('admin.annonces.store'), $this->formPayload());

        $response->assertRedirect(route('admin.annonces.index'));
        $this->assertDatabaseHas('annonces', [
            'paroisse_id' => $paroisse->id,
            'titre' => 'Annonce créée par formulaire',
            'contenu' => 'Contenu de test',
            'epinglee' => true,
        ]);
        $this->assertNotNull(Annonce::where('titre', 'Annonce créée par formulaire')->value('publie_le'));
    }

    public function test_secretariat_can_create_an_annonce_under_the_existing_policy(): void
    {
        $paroisse = Paroisse::factory()->create();

        $response = $this->actingAsSecretariat($paroisse)
            ->post(route('admin.annonces.store'), $this->formPayload());

        $response->assertRedirect(route('admin.annonces.index'));
        $this->assertDatabaseHas('annonces', [
            'paroisse_id' => $paroisse->id,
            'titre' => 'Annonce créée par formulaire',
        ]);
    }

    public function test_user_without_announcement_permission_is_forbidden(): void
    {
        $paroisse = Paroisse::factory()->create();
        $guest = User::factory()->create([
            'paroisse_id' => $paroisse->id,
            'role' => 'guest',
        ]);

        $response = $this->actingAs($guest)
            ->post(route('admin.annonces.store'), $this->formPayload());

        $response->assertForbidden();
        $this->assertDatabaseMissing('annonces', [
            'paroisse_id' => $paroisse->id,
            'titre' => 'Annonce créée par formulaire',
        ]);
    }

    public function test_admin_can_create_an_annonce_when_models_are_strict(): void
    {
        $paroisse = Paroisse::factory()->create();

        Model::shouldBeStrict();

        try {
            $response = $this->actingAsAdmin($paroisse)
                ->post(route('admin.annonces.store'), $this->formPayload());
        } finally {
            Model::shouldBeStrict(false);
        }

        $response->assertRedirect(route('admin.annonces.index'));
        $this->assertDatabaseHas('annonces', [
            'paroisse_id' => $paroisse->id,
            'titre' => 'Annonce créée par formulaire',
        ]);
    }

    /**
     * @return array{titre: string, contenu: string, epinglee: bool, publier: bool}
     */
    private function formPayload(): array
    {
        return [
            'titre' => 'Annonce créée par formulaire',
            'contenu' => 'Contenu de test',
            'epinglee' => true,
            'publier' => true,
        ];
    }
}
