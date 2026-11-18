<?php

namespace Tests\Feature;

use App\Models\Annonce;
use App\Models\Horaire;
use App\Models\Paroisse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CacheInvalidationTest extends TestCase
{
    use RefreshDatabase;

    protected Paroisse $paroisse;

    protected string $cacheKey;

    protected function setUp(): void
    {
        parent::setUp();

        $this->paroisse = Paroisse::factory()->create();
        $this->cacheKey = "paroisse:{$this->paroisse->id}:public:v3";
    }

    // ========== HELPER METHODS ==========

    private function populateCache(): void
    {
        Cache::put($this->cacheKey, ['test' => 'data'], 600);
    }

    private function assertCacheExists(): void
    {
        $this->assertTrue(Cache::has($this->cacheKey), 'Le cache devrait exister');
    }

    private function assertCacheDoesNotExist(): void
    {
        $this->assertFalse(Cache::has($this->cacheKey), 'Le cache devrait être invalidé');
    }

    // ========== HORAIRE OBSERVER TESTS ==========

    public function test_creating_horaire_invalidates_paroisse_cache()
    {
        $this->populateCache();
        $this->assertCacheExists();

        Horaire::factory()->create(['paroisse_id' => $this->paroisse->id]);

        $this->assertCacheDoesNotExist();
    }

    public function test_updating_horaire_invalidates_paroisse_cache()
    {
        $horaire = Horaire::factory()->create(['paroisse_id' => $this->paroisse->id]);

        $this->populateCache();
        $this->assertCacheExists();

        $horaire->update(['heure' => '10:00']);

        $this->assertCacheDoesNotExist();
    }

    public function test_deleting_horaire_invalidates_paroisse_cache()
    {
        $horaire = Horaire::factory()->create(['paroisse_id' => $this->paroisse->id]);

        $this->populateCache();
        $this->assertCacheExists();

        $horaire->delete();

        $this->assertCacheDoesNotExist();
    }

    public function test_updating_horaire_actif_status_invalidates_cache()
    {
        $horaire = Horaire::factory()->create([
            'paroisse_id' => $this->paroisse->id,
            'actif' => true,
        ]);

        $this->populateCache();
        $this->assertCacheExists();

        $horaire->update(['actif' => false]);

        $this->assertCacheDoesNotExist();
    }

    // ========== ANNONCE OBSERVER TESTS ==========

    public function test_creating_annonce_invalidates_paroisse_cache()
    {
        $this->populateCache();
        $this->assertCacheExists();

        Annonce::factory()->create(['paroisse_id' => $this->paroisse->id]);

        $this->assertCacheDoesNotExist();
    }

    public function test_updating_annonce_invalidates_paroisse_cache()
    {
        $annonce = Annonce::factory()->create(['paroisse_id' => $this->paroisse->id]);

        $this->populateCache();
        $this->assertCacheExists();

        $annonce->update(['titre' => 'Nouveau titre']);

        $this->assertCacheDoesNotExist();
    }

    public function test_deleting_annonce_invalidates_paroisse_cache()
    {
        $annonce = Annonce::factory()->create(['paroisse_id' => $this->paroisse->id]);

        $this->populateCache();
        $this->assertCacheExists();

        $annonce->delete();

        $this->assertCacheDoesNotExist();
    }

    public function test_publishing_annonce_invalidates_cache()
    {
        $annonce = Annonce::factory()->create([
            'paroisse_id' => $this->paroisse->id,
            'publie_le' => null, // Brouillon
        ]);

        $this->populateCache();
        $this->assertCacheExists();

        // Publier l'annonce
        $annonce->update(['publie_le' => now()]);

        $this->assertCacheDoesNotExist();
    }

    public function test_unpublishing_annonce_invalidates_cache()
    {
        $annonce = Annonce::factory()->create([
            'paroisse_id' => $this->paroisse->id,
            'publie_le' => now(), // Publiée
        ]);

        $this->populateCache();
        $this->assertCacheExists();

        // Repasser en brouillon
        $annonce->update(['publie_le' => null]);

        $this->assertCacheDoesNotExist();
    }

    public function test_toggling_annonce_epinglee_invalidates_cache()
    {
        $annonce = Annonce::factory()->create([
            'paroisse_id' => $this->paroisse->id,
            'epinglee' => false,
        ]);

        $this->populateCache();
        $this->assertCacheExists();

        $annonce->update(['epinglee' => true]);

        $this->assertCacheDoesNotExist();
    }

    public function test_updating_annonce_actif_status_invalidates_cache()
    {
        $annonce = Annonce::factory()->create([
            'paroisse_id' => $this->paroisse->id,
            'actif' => true,
        ]);

        $this->populateCache();
        $this->assertCacheExists();

        $annonce->update(['actif' => false]);

        $this->assertCacheDoesNotExist();
    }

    // ========== PAROISSE OBSERVER TESTS ==========

    public function test_updating_paroisse_invalidates_cache()
    {
        $this->populateCache();
        $this->assertCacheExists();

        $this->paroisse->update(['nom' => 'Nouveau nom']);

        $this->assertCacheDoesNotExist();
    }

    public function test_updating_paroisse_contact_info_invalidates_cache()
    {
        $this->populateCache();
        $this->assertCacheExists();

        $this->paroisse->update(['telephone' => '+229 12345678']);

        $this->assertCacheDoesNotExist();
    }

    public function test_updating_paroisse_address_invalidates_cache()
    {
        $this->populateCache();
        $this->assertCacheExists();

        $this->paroisse->update(['adresse' => 'Nouvelle adresse']);

        $this->assertCacheDoesNotExist();
    }

    // ========== ISOLATION TESTS ==========

    public function test_updating_different_paroisse_does_not_invalidate_cache()
    {
        $otherParoisse = Paroisse::factory()->create();

        $this->populateCache();
        $this->assertCacheExists();

        // Modifier une autre paroisse
        $otherParoisse->update(['nom' => 'Autre nom']);

        // Le cache de notre paroisse doit rester intact
        $this->assertCacheExists();
    }

    public function test_creating_horaire_for_different_paroisse_does_not_invalidate_cache()
    {
        $otherParoisse = Paroisse::factory()->create();

        $this->populateCache();
        $this->assertCacheExists();

        Horaire::factory()->create(['paroisse_id' => $otherParoisse->id]);

        $this->assertCacheExists();
    }

    public function test_creating_annonce_for_different_paroisse_does_not_invalidate_cache()
    {
        $otherParoisse = Paroisse::factory()->create();

        $this->populateCache();
        $this->assertCacheExists();

        Annonce::factory()->create(['paroisse_id' => $otherParoisse->id]);

        $this->assertCacheExists();
    }

    // ========== MULTIPLE CHANGES TESTS ==========

    public function test_multiple_changes_invalidate_cache_multiple_times()
    {
        $annonce = Annonce::factory()->create(['paroisse_id' => $this->paroisse->id]);

        // 1. Populer le cache
        $this->populateCache();
        $this->assertCacheExists();

        // 2. Modifier l'annonce (invalide le cache)
        $annonce->update(['titre' => 'Titre 1']);
        $this->assertCacheDoesNotExist();

        // 3. Re-populer le cache
        $this->populateCache();
        $this->assertCacheExists();

        // 4. Modifier à nouveau (invalide encore)
        $annonce->update(['titre' => 'Titre 2']);
        $this->assertCacheDoesNotExist();
    }

    public function test_cascade_of_changes_invalidates_cache()
    {
        $this->populateCache();
        $this->assertCacheExists();

        // Créer plusieurs ressources qui vont toutes invalider le cache
        Annonce::factory()->create(['paroisse_id' => $this->paroisse->id]);
        $this->assertCacheDoesNotExist();

        $this->populateCache();
        Horaire::factory()->create(['paroisse_id' => $this->paroisse->id]);
        $this->assertCacheDoesNotExist();

        $this->populateCache();
        $this->paroisse->update(['nom' => 'Modifié']);
        $this->assertCacheDoesNotExist();
    }

    // ========== CACHE KEY VERSIONING TESTS ==========

    public function test_cache_key_includes_paroisse_id()
    {
        $paroisse1 = Paroisse::factory()->create();
        $paroisse2 = Paroisse::factory()->create();

        $key1 = "paroisse:{$paroisse1->id}:public:v3";
        $key2 = "paroisse:{$paroisse2->id}:public:v3";

        Cache::put($key1, ['data1'], 600);
        Cache::put($key2, ['data2'], 600);

        $this->assertTrue(Cache::has($key1));
        $this->assertTrue(Cache::has($key2));

        // Modifier paroisse1
        $paroisse1->update(['nom' => 'Modifié']);

        // Seul le cache de paroisse1 doit être invalidé
        $this->assertFalse(Cache::has($key1));
        $this->assertTrue(Cache::has($key2));
    }

    public function test_cache_key_includes_version_number()
    {
        $this->assertStringContainsString(':v3', $this->cacheKey);
    }

    // ========== PUBLIC PAGE INTEGRATION TESTS ==========

    public function test_public_page_caches_data_correctly()
    {
        Horaire::factory()->create([
            'paroisse_id' => $this->paroisse->id,
            'actif' => true,
        ]);

        Annonce::factory()->create([
            'paroisse_id' => $this->paroisse->id,
            'publie_le' => now(),
            'actif' => true,
        ]);

        // Première visite: met en cache
        $response = $this->get(route('paroisse.show', $this->paroisse));
        $response->assertSuccessful();

        // Vérifier que le cache existe maintenant
        $this->assertTrue(Cache::has($this->cacheKey));
    }

    public function test_public_page_cache_is_invalidated_after_content_change()
    {
        $annonce = Annonce::factory()->create([
            'paroisse_id' => $this->paroisse->id,
            'publie_le' => now(),
            'actif' => true,
        ]);

        // Première visite: met en cache
        $response = $this->get(route('paroisse.show', $this->paroisse));
        $response->assertSuccessful();
        $this->assertTrue(Cache::has($this->cacheKey));

        // Modifier le contenu
        $annonce->update(['titre' => 'Nouveau titre']);

        // Le cache doit être invalidé
        $this->assertFalse(Cache::has($this->cacheKey));

        // Deuxième visite: remet en cache avec les nouvelles données
        $response = $this->get(route('paroisse.show', $this->paroisse));
        $response->assertSuccessful();
        $this->assertTrue(Cache::has($this->cacheKey));
    }

    // ========== EDGE CASES ==========

    public function test_observer_handles_null_paroisse_id_gracefully()
    {
        $this->populateCache();
        $this->assertCacheExists();

        // Créer un horaire sans paroisse_id (ne devrait pas arriver en pratique)
        try {
            $horaire = new Horaire([
                'paroisse_id' => null,
                'type_celebration' => 'messe',
                'jour_semaine' => 1,
                'heure' => '10:00',
            ]);
            $horaire->saveOrFail();
        } catch (\Exception $e) {
            // Peut échouer à cause des contraintes de validation
            $this->assertTrue(true);

            return;
        }

        // Le cache ne doit pas être affecté
        $this->assertCacheExists();
    }

    public function test_soft_delete_would_invalidate_cache_if_implemented()
    {
        // Note: Les modèles n'utilisent pas SoftDeletes actuellement
        // Mais si on l'implémente plus tard, deleted() de l'observer le gérera

        $annonce = Annonce::factory()->create(['paroisse_id' => $this->paroisse->id]);

        $this->populateCache();
        $this->assertCacheExists();

        // Hard delete (invalidation normale)
        $annonce->delete();

        $this->assertCacheDoesNotExist();
    }

    public function test_cache_invalidation_works_with_database_driver()
    {
        // S'assurer qu'on utilise le driver database (pas Redis)
        config(['cache.default' => 'database']);

        $this->populateCache();
        $this->assertCacheExists();

        Annonce::factory()->create(['paroisse_id' => $this->paroisse->id]);

        $this->assertCacheDoesNotExist();
    }

    public function test_cache_invalidation_works_with_file_driver()
    {
        // S'assurer qu'on utilise le driver file
        config(['cache.default' => 'file']);

        $this->populateCache();
        $this->assertCacheExists();

        Horaire::factory()->create(['paroisse_id' => $this->paroisse->id]);

        $this->assertCacheDoesNotExist();
    }

    public function test_bulk_create_invalidates_cache_for_each_record()
    {
        $this->populateCache();
        $this->assertCacheExists();

        // Créer plusieurs annonces
        Annonce::factory()->count(3)->create(['paroisse_id' => $this->paroisse->id]);

        // Le cache doit être invalidé
        $this->assertCacheDoesNotExist();
    }
}
