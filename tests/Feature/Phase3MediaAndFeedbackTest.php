<?php

namespace Tests\Feature;

use App\Models\Activite;
use App\Models\Annonce;
use App\Models\Avis;
use App\Models\Paroisse;
use App\Models\User;
use App\Support\PublicFormToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Phase3MediaAndFeedbackTest extends TestCase
{
    use RefreshDatabase;

    private function adminFor(Paroisse $paroisse): User
    {
        return User::factory()->create([
            'paroisse_id' => $paroisse->id,
            'role' => 'admin',
        ]);
    }

    public function test_admin_can_upload_parish_images_and_set_whatsapp_link(): void
    {
        Storage::fake('public');
        $paroisse = Paroisse::factory()->create();
        $admin = $this->adminFor($paroisse);

        $response = $this->actingAs($admin)->put(route('admin.paroisse.update'), [
            'nom' => $paroisse->nom,
            'couleur_principale' => '#7C3AED',
            'whatsapp_channel_url' => 'https://chat.whatsapp.com/exemple',
            'logo' => UploadedFile::fake()->image('logo.png', 120, 120),
            'banniere' => UploadedFile::fake()->image('banniere.jpg', 640, 320),
        ]);

        $response->assertSessionHasNoErrors();
        $paroisse->refresh();

        $this->assertNotNull($paroisse->logo_path);
        $this->assertNotNull($paroisse->banniere_path);
        $this->assertSame('https://chat.whatsapp.com/exemple', $paroisse->whatsapp_channel_url);
        Storage::disk('public')->assertExists($paroisse->logo_path);
        Storage::disk('public')->assertExists($paroisse->banniere_path);
        $this->assertSame('#7C3AED', $paroisse->couleur_principale);
    }

    public function test_whatsapp_link_is_restricted_to_whatsapp_domains(): void
    {
        $paroisse = Paroisse::factory()->create();
        $admin = $this->adminFor($paroisse);

        $this->actingAs($admin)->put(route('admin.paroisse.update'), [
            'nom' => $paroisse->nom,
            'whatsapp_channel_url' => 'https://exemple.com/groupe',
        ])->assertSessionHasErrors('whatsapp_channel_url');
    }

    public function test_announce_image_is_uploaded_and_exposed_on_public_page(): void
    {
        Storage::fake('public');
        $paroisse = Paroisse::factory()->create();
        $admin = $this->adminFor($paroisse);

        $this->actingAs($admin)->post(route('admin.annonces.store'), [
            'titre' => 'Messe de la paroisse',
            'contenu' => 'Venez nombreux.',
            'categorie' => 'liturgie',
            'publier' => true,
            'image' => UploadedFile::fake()->image('annonce.png', 400, 300),
        ])->assertSessionHasNoErrors();

        $annonce = Annonce::query()->firstOrFail();
        $this->assertNotNull($annonce->image_path);
        Storage::disk('public')->assertExists($annonce->image_path);

        $props = $this->get(route('paroisse.show', $paroisse))->viewData('page')['props'];
        $this->assertNotEmpty($props['annonces']);
        $this->assertNotNull($props['annonces'][0]['image_url']);
        $this->assertStringContainsString(basename($annonce->image_path), $props['annonces'][0]['image_url']);
    }

    public function test_activity_dashboard_is_scoped_to_the_parish(): void
    {
        $paroisseA = Paroisse::factory()->create();
        $paroisseB = Paroisse::factory()->create();
        $adminA = $this->adminFor($paroisseA);
        $adminB = $this->adminFor($paroisseB);

        Activite::factory()->create([
            'paroisse_id' => $paroisseA->id,
            'user_id' => $adminA->id,
            'action' => 'annonce.cree',
        ]);
        Activite::factory()->create([
            'paroisse_id' => $paroisseB->id,
            'user_id' => $adminB->id,
            'action' => 'annonce.supprimee',
        ]);

        $propsA = $this->actingAs($adminA)->get(route('admin.activites.index'))
            ->assertOk()->viewData('page')['props'];
        $this->assertCount(1, $propsA['activites']['data']);
        $this->assertSame('annonce.cree', $propsA['activites']['data'][0]['action']);
        $this->assertGreaterThanOrEqual(1, count($propsA['utilisateurs']));

        $superAdmin = User::factory()->create([
            'paroisse_id' => null,
            'role' => 'super_admin',
        ]);

        $propsSuper = $this->actingAs($superAdmin)->get(route('admin.activites.index'))
            ->assertOk()->viewData('page')['props'];
        $this->assertCount(2, $propsSuper['activites']['data']);
        $this->assertCount(3, $propsSuper['utilisateurs']);

        $this->actingAs($adminB)->get(route('admin.activites.index'))->assertOk();
    }

    public function test_public_avis_is_anonymous_and_requires_moderation(): void
    {
        $paroisse = Paroisse::factory()->create();
        $token = PublicFormToken::issue(now()->subSeconds(10));

        $this->post(route('paroisse.avis.store', $paroisse), [
            'message' => 'Merci pour l’accueil dimanche.',
            'reference_colis' => '',
            'form_token' => $token,
        ])->assertSessionHasNoErrors();

        $avis = Avis::query()->firstOrFail();
        $this->assertSame('en_attente', $avis->statut);
        $this->assertSame($paroisse->id, $avis->paroisse_id);
        $this->assertArrayNotHasKey('ip', $avis->getAttributes());
        $this->assertArrayNotHasKey('nom', $avis->getAttributes());

        $props = $this->get(route('paroisse.show', $paroisse))->viewData('page')['props'];
        $this->assertCount(0, $props['avis']);

        $admin = $this->adminFor($paroisse);
        $this->actingAs($admin)->patch(route('admin.avis.update', $avis), [
            'statut' => 'publie',
        ])->assertSessionHasNoErrors();

        $props = $this->get(route('paroisse.show', $paroisse))->viewData('page')['props'];
        $this->assertCount(1, $props['avis']);
        $this->assertSame('Merci pour l’accueil dimanche.', $props['avis'][0]['message']);
    }

    public function test_avis_form_rejects_missing_token_and_html(): void
    {
        $paroisse = Paroisse::factory()->create();

        $this->post(route('paroisse.avis.store', $paroisse), [
            'message' => '<script>alert(1)</script>',
            'form_token' => PublicFormToken::issue(now()->subSeconds(10)),
        ])->assertSessionHasErrors('message');
    }

    public function test_qr_code_endpoint_returns_svg(): void
    {
        $paroisse = Paroisse::factory()->create();
        $admin = $this->adminFor($paroisse);

        $response = $this->actingAs($admin)->get(route('admin.paroisse.qr-code'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/svg+xml');
        $this->assertStringContainsString('<svg', $response->getContent());
    }
}
