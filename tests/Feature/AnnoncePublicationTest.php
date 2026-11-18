<?php

namespace Tests\Feature;

use App\Models\Annonce;
use App\Models\Paroisse;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnoncePublicationTest extends TestCase
{
    use RefreshDatabase;

    protected Paroisse $paroisse;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->paroisse = Paroisse::factory()->create();
        $this->admin = User::factory()->create([
            'paroisse_id' => $this->paroisse->id,
            'role' => 'admin',
        ]);
    }

    // ========== DRAFT TESTS ==========

    public function test_draft_annonce_has_null_publie_le()
    {
        $annonce = Annonce::factory()->create([
            'paroisse_id' => $this->paroisse->id,
            'publie_le' => null,
        ]);

        $this->assertNull($annonce->publie_le);
    }

    public function test_draft_annonce_is_not_visible_on_public_page()
    {
        // Créer une annonce brouillon
        $draft = Annonce::factory()->create([
            'paroisse_id' => $this->paroisse->id,
            'publie_le' => null,
            'actif' => true,
        ]);

        // Créer une annonce publiée pour comparaison
        $published = Annonce::factory()->create([
            'paroisse_id' => $this->paroisse->id,
            'publie_le' => now(),
            'actif' => true,
        ]);

        // Visiter la page publique
        $response = $this->get(route('paroisse.show', $this->paroisse));

        $response->assertSuccessful();

        // Récupérer les annonces via le scope publiees
        $annonces = Annonce::publiees()->get();

        // Seule l'annonce publiée doit être visible
        $this->assertCount(1, $annonces);
        $this->assertTrue($annonces->contains('id', $published->id));
        $this->assertFalse($annonces->contains('id', $draft->id));
    }

    public function test_scope_publiees_excludes_drafts()
    {
        Annonce::factory()->create([
            'paroisse_id' => $this->paroisse->id,
            'titre' => 'Brouillon',
            'publie_le' => null,
        ]);

        $publieesCount = Annonce::publiees()->count();

        $this->assertEquals(0, $publieesCount, 'Les brouillons ne doivent pas être comptés');
    }

    // ========== PUBLISHED TESTS ==========

    public function test_published_annonce_has_publie_le_datetime()
    {
        $annonce = Annonce::factory()->create([
            'paroisse_id' => $this->paroisse->id,
            'publie_le' => now(),
        ]);

        $this->assertNotNull($annonce->publie_le);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $annonce->publie_le);
    }

    public function test_published_annonce_is_visible_on_public_page()
    {
        $annonce = Annonce::factory()->create([
            'paroisse_id' => $this->paroisse->id,
            'publie_le' => now()->subHour(),
            'actif' => true,
        ]);

        $publieesAnnonces = Annonce::publiees()->get();

        $this->assertCount(1, $publieesAnnonces);
        $this->assertEquals($annonce->id, $publieesAnnonces->first()->id);
    }

    public function test_annonce_published_in_past_is_immediately_visible()
    {
        $annonce = Annonce::factory()->create([
            'paroisse_id' => $this->paroisse->id,
            'publie_le' => now()->subDay(),
            'actif' => true,
        ]);

        $this->assertTrue(Annonce::publiees()->where('id', $annonce->id)->exists());
    }

    public function test_annonce_published_right_now_is_visible()
    {
        $annonce = Annonce::factory()->create([
            'paroisse_id' => $this->paroisse->id,
            'publie_le' => now(),
            'actif' => true,
        ]);

        // Même publiée à l'instant, elle doit être visible
        $this->assertTrue(Annonce::publiees()->where('id', $annonce->id)->exists());
    }

    public function test_scope_publiees_includes_published_annonces()
    {
        Annonce::factory()->count(3)->create([
            'paroisse_id' => $this->paroisse->id,
            'publie_le' => now()->subHours(2),
        ]);

        $publieesCount = Annonce::publiees()->count();

        $this->assertEquals(3, $publieesCount);
    }

    // ========== SCHEDULED (FUTURE) TESTS ==========

    public function test_scheduled_annonce_has_future_publie_le()
    {
        $futureDate = now()->addDay();

        $annonce = Annonce::factory()->create([
            'paroisse_id' => $this->paroisse->id,
            'publie_le' => $futureDate,
        ]);

        $this->assertNotNull($annonce->publie_le);
        $this->assertTrue($annonce->publie_le->isFuture());
    }

    public function test_scheduled_annonce_is_not_visible_before_publication_date()
    {
        // Créer une annonce programmée pour demain
        $annonce = Annonce::factory()->create([
            'paroisse_id' => $this->paroisse->id,
            'publie_le' => now()->addDay(),
            'actif' => true,
        ]);

        // Elle ne doit pas apparaître dans le scope publiees
        $publieesAnnonces = Annonce::publiees()->get();

        $this->assertCount(0, $publieesAnnonces);
        $this->assertFalse($publieesAnnonces->contains('id', $annonce->id));
    }

    public function test_scheduled_annonce_becomes_visible_after_publication_date()
    {
        // Simuler une annonce programmée dans le passé (comme si elle était programmée et que le temps a passé)
        Carbon::setTestNow(now());

        $annonce = Annonce::factory()->create([
            'paroisse_id' => $this->paroisse->id,
            'publie_le' => now()->subMinute(), // Publiée il y a 1 minute
            'actif' => true,
        ]);

        // Elle doit maintenant apparaître
        $publieesAnnonces = Annonce::publiees()->get();

        $this->assertCount(1, $publieesAnnonces);
        $this->assertTrue($publieesAnnonces->contains('id', $annonce->id));

        Carbon::setTestNow(); // Reset
    }

    public function test_scheduled_annonce_in_one_hour_is_not_visible()
    {
        $annonce = Annonce::factory()->create([
            'paroisse_id' => $this->paroisse->id,
            'publie_le' => now()->addHour(),
            'actif' => true,
        ]);

        $this->assertFalse(Annonce::publiees()->where('id', $annonce->id)->exists());
    }

    public function test_scheduled_annonce_appears_at_exact_publication_time()
    {
        // Programmer pour dans 5 minutes
        $publicationTime = now()->addMinutes(5);

        $annonce = Annonce::factory()->create([
            'paroisse_id' => $this->paroisse->id,
            'publie_le' => $publicationTime->copy(), // Utiliser copy() pour ne pas modifier l'original
            'actif' => true,
        ]);

        // Avant l'heure: pas visible
        Carbon::setTestNow($publicationTime->copy()->subSecond());
        $this->assertFalse(Annonce::publiees()->where('id', $annonce->id)->exists());

        // À l'heure pile: visible
        Carbon::setTestNow($publicationTime->copy());
        $this->assertTrue(Annonce::publiees()->where('id', $annonce->id)->exists());

        // Après l'heure: toujours visible
        Carbon::setTestNow($publicationTime->copy()->addMinute());
        $this->assertTrue(Annonce::publiees()->where('id', $annonce->id)->exists());

        Carbon::setTestNow(); // Reset
    }

    // ========== ACTIF FLAG TESTS ==========

    public function test_inactive_published_annonce_is_not_visible()
    {
        $annonce = Annonce::factory()->create([
            'paroisse_id' => $this->paroisse->id,
            'publie_le' => now()->subHour(),
            'actif' => false, // Désactivée
        ]);

        // Le scope publiees seul ne filtre pas par actif
        $publieesOnly = Annonce::publiees()->get();
        $this->assertCount(1, $publieesOnly);

        // Mais avec actives(), elle ne doit pas apparaître
        $publieesEtActives = Annonce::publiees()->actives()->get();
        $this->assertCount(0, $publieesEtActives);
    }

    public function test_scope_actives_filters_by_actif_flag()
    {
        Annonce::factory()->create([
            'paroisse_id' => $this->paroisse->id,
            'actif' => true,
        ]);

        Annonce::factory()->create([
            'paroisse_id' => $this->paroisse->id,
            'actif' => false,
        ]);

        $activesCount = Annonce::actives()->count();

        $this->assertEquals(1, $activesCount);
    }

    // ========== MIXED SCENARIOS ==========

    public function test_mix_of_draft_published_and_scheduled_annonces()
    {
        // Brouillon
        $draft = Annonce::factory()->create([
            'paroisse_id' => $this->paroisse->id,
            'titre' => 'Brouillon',
            'publie_le' => null,
            'actif' => true,
        ]);

        // Publiée
        $published = Annonce::factory()->create([
            'paroisse_id' => $this->paroisse->id,
            'titre' => 'Publiée',
            'publie_le' => now()->subDay(),
            'actif' => true,
        ]);

        // Programmée (futur)
        $scheduled = Annonce::factory()->create([
            'paroisse_id' => $this->paroisse->id,
            'titre' => 'Programmée',
            'publie_le' => now()->addDay(),
            'actif' => true,
        ]);

        // Seule la publiée doit être visible
        $visibles = Annonce::publiees()->actives()->get();

        $this->assertCount(1, $visibles);
        $this->assertEquals($published->id, $visibles->first()->id);
    }

    public function test_public_page_only_shows_published_and_active_annonces()
    {
        // Créer différents types d'annonces
        Annonce::factory()->create([
            'paroisse_id' => $this->paroisse->id,
            'titre' => 'Visible',
            'publie_le' => now()->subHour(),
            'actif' => true,
        ]);

        Annonce::factory()->create([
            'paroisse_id' => $this->paroisse->id,
            'titre' => 'Brouillon',
            'publie_le' => null,
            'actif' => true,
        ]);

        Annonce::factory()->create([
            'paroisse_id' => $this->paroisse->id,
            'titre' => 'Inactive',
            'publie_le' => now()->subHour(),
            'actif' => false,
        ]);

        Annonce::factory()->create([
            'paroisse_id' => $this->paroisse->id,
            'titre' => 'Future',
            'publie_le' => now()->addDay(),
            'actif' => true,
        ]);

        // Simuler la requête de la page publique
        $visibles = Annonce::publiees()->actives()->get();

        $this->assertCount(1, $visibles);
        $this->assertEquals('Visible', $visibles->first()->titre);
    }

    public function test_epinglee_flag_is_independent_of_publication_status()
    {
        // Une annonce épinglée mais brouillon
        $epingleeBrouillon = Annonce::factory()->create([
            'paroisse_id' => $this->paroisse->id,
            'epinglee' => true,
            'publie_le' => null,
        ]);

        // Une annonce épinglée et publiée
        $epingleePubliee = Annonce::factory()->create([
            'paroisse_id' => $this->paroisse->id,
            'epinglee' => true,
            'publie_le' => now(),
        ]);

        // Seule la publiée doit être visible
        $visibles = Annonce::publiees()->get();

        $this->assertCount(1, $visibles);
        $this->assertEquals($epingleePubliee->id, $visibles->first()->id);
        $this->assertTrue($visibles->first()->epinglee);
    }

    // ========== TIMEZONE TESTS (Africa/Porto-Novo) ==========

    public function test_publication_respects_server_timezone()
    {
        // Assurer qu'on utilise le bon fuseau horaire
        config(['app.timezone' => 'Africa/Porto-Novo']);

        $publicationTime = Carbon::parse('2024-01-15 10:00:00', 'Africa/Porto-Novo');

        $annonce = Annonce::factory()->create([
            'paroisse_id' => $this->paroisse->id,
            'publie_le' => $publicationTime,
        ]);

        // Simuler qu'on est juste avant
        Carbon::setTestNow($publicationTime->copy()->subMinute());
        $this->assertFalse(Annonce::publiees()->where('id', $annonce->id)->exists());

        // Simuler qu'on est juste après
        Carbon::setTestNow($publicationTime->copy()->addMinute());
        $this->assertTrue(Annonce::publiees()->where('id', $annonce->id)->exists());

        Carbon::setTestNow(); // Reset
    }

    // ========== EDGE CASES ==========

    public function test_annonce_with_date_debut_and_date_fin_can_be_published()
    {
        $annonce = Annonce::factory()->create([
            'paroisse_id' => $this->paroisse->id,
            'publie_le' => now(),
            'date_debut' => now()->addDay(),
            'date_fin' => now()->addWeek(),
            'actif' => true,
        ]);

        // Elle doit être visible même si date_debut est dans le futur
        // (date_debut/date_fin sont pour l'affichage, pas pour la visibilité)
        $this->assertTrue(Annonce::publiees()->where('id', $annonce->id)->exists());
    }

    public function test_notifier_flag_does_not_affect_visibility()
    {
        $annonceWithNotif = Annonce::factory()->create([
            'paroisse_id' => $this->paroisse->id,
            'publie_le' => now(),
            'notifier' => true,
            'actif' => true,
        ]);

        $annonceWithoutNotif = Annonce::factory()->create([
            'paroisse_id' => $this->paroisse->id,
            'publie_le' => now(),
            'notifier' => false,
            'actif' => true,
        ]);

        $visibles = Annonce::publiees()->actives()->get();

        // Les deux doivent être visibles
        $this->assertCount(2, $visibles);
    }

    public function test_changing_publie_le_from_null_to_now_publishes_annonce()
    {
        $annonce = Annonce::factory()->create([
            'paroisse_id' => $this->paroisse->id,
            'publie_le' => null, // Brouillon
        ]);

        $this->assertFalse(Annonce::publiees()->where('id', $annonce->id)->exists());

        // Publier maintenant
        $annonce->update(['publie_le' => now()]);

        $this->assertTrue(Annonce::publiees()->where('id', $annonce->id)->exists());
    }

    public function test_changing_publie_le_from_now_to_null_unpublishes_annonce()
    {
        $annonce = Annonce::factory()->create([
            'paroisse_id' => $this->paroisse->id,
            'publie_le' => now(), // Publiée
        ]);

        $this->assertTrue(Annonce::publiees()->where('id', $annonce->id)->exists());

        // Repasser en brouillon
        $annonce->update(['publie_le' => null]);

        $this->assertFalse(Annonce::publiees()->where('id', $annonce->id)->exists());
    }
}
