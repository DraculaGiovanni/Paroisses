<?php

namespace Tests\Feature;

use App\Models\Annonce;
use App\Models\Horaire;
use App\Models\Paroisse;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class PublicParoisseControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $cacheDirectory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cacheDirectory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'paroisses-cache-'.Str::uuid();
        File::ensureDirectoryExists($this->cacheDirectory);

        config([
            'cache.default' => 'file',
            'cache.stores.file.path' => $this->cacheDirectory,
            'cache.stores.file.lock_path' => $this->cacheDirectory,
        ]);
        Cache::setDefaultDriver('file');
        Cache::store('file')->flush();
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->cacheDirectory);

        parent::tearDown();
    }

    public function test_public_page_exposes_paroisse_slug_for_client_side_routes(): void
    {
        $paroisse = Paroisse::factory()->create(['slug' => 'saint-michel']);

        $response = $this->get(route('paroisse.show', $paroisse));

        $response->assertOk();
        $this->assertSame('saint-michel', $this->pageProps($response)['paroisse']['slug']);
    }

    public function test_public_page_round_trips_array_cache_without_content_queries_or_frozen_time(): void
    {
        $this->travelTo(now('Africa/Porto-Novo')->setDate(2026, 11, 17)->setTime(10, 59));

        $paroisse = Paroisse::factory()->create();

        Annonce::factory()->create([
            'paroisse_id' => $paroisse->id,
            'titre' => 'Annonce publiée',
            'publie_le' => now()->subMinute(),
            'actif' => true,
        ]);
        Annonce::factory()->create([
            'paroisse_id' => $paroisse->id,
            'titre' => 'Deuxième annonce',
            'publie_le' => now()->subMinute(),
            'actif' => true,
        ]);
        Horaire::factory()->create([
            'paroisse_id' => $paroisse->id,
            'type' => 'messe',
            'jour_semaine' => 2,
            'heure' => '11:00',
            'actif' => true,
        ]);
        Horaire::factory()->create([
            'paroisse_id' => $paroisse->id,
            'type' => 'messe',
            'jour_semaine' => 2,
            'heure' => '12:00',
            'actif' => true,
        ]);

        $firstResponse = $this->get(route('paroisse.show', $paroisse));
        $firstResponse->assertOk();
        $firstProps = $this->pageProps($firstResponse);

        $cacheKey = "paroisse:{$paroisse->id}:public:v3";
        $cachedContent = Cache::get($cacheKey);

        $this->assertSame(['horaires', 'annonces', 'avis'], array_keys($cachedContent));
        $this->assertIsArray($cachedContent['horaires']);
        $this->assertIsArray($cachedContent['annonces']);
        $this->assertIsArray($cachedContent['avis']);
        $this->assertTrue(array_is_list($cachedContent['horaires']));
        $this->assertTrue(array_is_list($cachedContent['annonces']));
        $this->assertTrue($this->containsOnlyArraysAndScalars($cachedContent));
        $this->assertCount(2, $cachedContent['horaires']);
        $this->assertCount(2, $cachedContent['annonces']);
        $this->assertArrayNotHasKey('server_time', $cachedContent);
        $this->assertArrayNotHasKey('prochaine_messe', $cachedContent);
        $this->assertIsArray($firstProps['horaires']);
        $this->assertIsArray($firstProps['annonces']);
        $this->assertIsArray($firstProps['horaires'][0]);
        $this->assertIsArray($firstProps['annonces'][0]);
        $this->assertSame('11:00', $firstProps['prochaine_messe']['heure']);

        $queries = [];
        DB::listen(function (QueryExecuted $query) use (&$queries): void {
            $queries[] = $query->sql;
        });

        $this->travelTo(now('Africa/Porto-Novo')->setTime(11, 0, 1));

        $secondResponse = $this->get(route('paroisse.show', $paroisse));
        $secondResponse->assertOk();
        $secondProps = $this->pageProps($secondResponse);

        $this->assertSame(count($firstProps['annonces']), count($secondProps['annonces']));
        $this->assertSame(count($firstProps['horaires']), count($secondProps['horaires']));
        $this->assertNotSame($firstProps['server_time'], $secondProps['server_time']);
        $this->assertSame('12:00', $secondProps['prochaine_messe']['heure']);
        $this->assertSame([], array_values(array_filter(
            $queries,
            fn (string $sql): bool => str_contains(strtolower($sql), 'annonces')
                || str_contains(strtolower($sql), 'horaires')
        )));
    }

    public function test_public_page_replaces_a_serialized_object_cache_value(): void
    {
        $paroisse = Paroisse::factory()->create();
        Annonce::factory()->create([
            'paroisse_id' => $paroisse->id,
            'titre' => 'Annonce publiable',
            'publie_le' => now()->subMinute(),
            'actif' => true,
        ]);
        Horaire::factory()->create([
            'paroisse_id' => $paroisse->id,
            'type' => 'messe',
            'actif' => true,
        ]);

        Cache::put("paroisse:{$paroisse->id}:public:v3", new \stdClass, 600);

        $response = $this->get(route('paroisse.show', $paroisse));
        $response->assertOk();
        $props = $this->pageProps($response);
        $replacedCache = Cache::get("paroisse:{$paroisse->id}:public:v3");

        $this->assertCount(1, $props['annonces']);
        $this->assertCount(1, $props['horaires']);
        $this->assertIsArray($replacedCache);
        $this->assertSame(['horaires', 'annonces', 'avis'], array_keys($replacedCache));
    }

    public function test_public_page_excludes_draft_and_scheduled_annonces_from_cold_and_warm_cache(): void
    {
        $paroisse = Paroisse::factory()->create();

        Annonce::factory()->create([
            'paroisse_id' => $paroisse->id,
            'titre' => 'Publiée',
            'publie_le' => now()->subMinute(),
            'actif' => true,
        ]);
        Annonce::factory()->create([
            'paroisse_id' => $paroisse->id,
            'titre' => 'Brouillon',
            'publie_le' => null,
            'actif' => true,
        ]);
        Annonce::factory()->create([
            'paroisse_id' => $paroisse->id,
            'titre' => 'Programmée',
            'publie_le' => now()->addDay(),
            'actif' => true,
        ]);

        $firstResponse = $this->get(route('paroisse.show', $paroisse));
        $firstResponse->assertOk();
        $secondResponse = $this->get(route('paroisse.show', $paroisse));
        $secondResponse->assertOk();

        $this->assertSame(['Publiée'], array_column($this->pageProps($firstResponse)['annonces'], 'titre'));
        $this->assertSame(['Publiée'], array_column($this->pageProps($secondResponse)['annonces'], 'titre'));
    }

    public function test_public_page_reflects_announcement_creation_update_and_deletion(): void
    {
        $paroisse = Paroisse::factory()->create();
        $emptyResponse = $this->get(route('paroisse.show', $paroisse));
        $emptyResponse->assertOk();
        $this->assertCount(0, $this->pageProps($emptyResponse)['annonces']);

        $annonce = Annonce::factory()->create([
            'paroisse_id' => $paroisse->id,
            'titre' => 'Annonce créée',
            'publie_le' => now()->subMinute(),
            'actif' => true,
        ]);
        $createdResponse = $this->get(route('paroisse.show', $paroisse));
        $createdResponse->assertOk();
        $this->assertSame('Annonce créée', $this->pageProps($createdResponse)['annonces'][0]['titre']);

        $annonce->update(['titre' => 'Annonce modifiée']);
        $updatedResponse = $this->get(route('paroisse.show', $paroisse));
        $updatedResponse->assertOk();
        $this->assertSame('Annonce modifiée', $this->pageProps($updatedResponse)['annonces'][0]['titre']);

        $annonce->delete();
        $deletedResponse = $this->get(route('paroisse.show', $paroisse));
        $deletedResponse->assertOk();
        $this->assertCount(0, $this->pageProps($deletedResponse)['annonces']);
    }

    public function test_public_page_reflects_schedule_creation_update_and_deletion(): void
    {
        $paroisse = Paroisse::factory()->create();
        $emptyResponse = $this->get(route('paroisse.show', $paroisse));
        $emptyResponse->assertOk();
        $this->assertCount(0, $this->pageProps($emptyResponse)['horaires']);

        $horaire = Horaire::factory()->create([
            'paroisse_id' => $paroisse->id,
            'type' => 'messe',
            'heure' => '11:00',
            'actif' => true,
        ]);
        $createdResponse = $this->get(route('paroisse.show', $paroisse));
        $createdResponse->assertOk();
        $this->assertSame('11:00', $this->pageProps($createdResponse)['horaires'][0]['heure']);

        $horaire->update(['heure' => '12:00']);
        $updatedResponse = $this->get(route('paroisse.show', $paroisse));
        $updatedResponse->assertOk();
        $this->assertSame('12:00', $this->pageProps($updatedResponse)['horaires'][0]['heure']);

        $horaire->delete();
        $deletedResponse = $this->get(route('paroisse.show', $paroisse));
        $deletedResponse->assertOk();
        $this->assertCount(0, $this->pageProps($deletedResponse)['horaires']);
    }

    private function pageProps(TestResponse $response): array
    {
        $props = [];

        $response->assertInertia(function (AssertableInertia $page) use (&$props): void {
            $page->component('Public/Paroisse');
            $props = $page->toArray()['props'];
        });

        return $props;
    }

    private function containsOnlyArraysAndScalars(mixed $value): bool
    {
        if (! is_array($value)) {
            return is_scalar($value) || $value === null;
        }

        foreach ($value as $item) {
            if (! $this->containsOnlyArraysAndScalars($item)) {
                return false;
            }
        }

        return true;
    }
}
