<?php

namespace Tests\Feature;

use App\Models\Annonce;
use App\Models\Horaire;
use App\Models\Paroisse;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DashboardStatsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function dashboard_shows_correct_annonces_stats()
    {
        $paroisse = Paroisse::factory()->create();
        $admin = User::factory()->admin()->create(['paroisse_id' => $paroisse->id]);

        // Créer des annonces de différents types
        Annonce::factory()->create([
            'paroisse_id' => $paroisse->id,
            'publie_le' => null, // brouillon
        ]);

        Annonce::factory()->create([
            'paroisse_id' => $paroisse->id,
            'publie_le' => Carbon::now()->subDay(), // publiée
        ]);

        Annonce::factory()->create([
            'paroisse_id' => $paroisse->id,
            'publie_le' => Carbon::now()->addDay(), // programmée
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertSuccessful();

        $stats = $response->viewData('page')['props']['stats'];

        $this->assertEquals(1, $stats['annonces_brouillons']);
        $this->assertEquals(1, $stats['annonces_publiees']);
        $this->assertEquals(1, $stats['annonces_programmees']);
    }

    #[Test]
    public function dashboard_shows_correct_horaires_count()
    {
        $paroisse = Paroisse::factory()->create();
        $admin = User::factory()->admin()->create(['paroisse_id' => $paroisse->id]);

        Horaire::factory()->count(3)->create([
            'paroisse_id' => $paroisse->id,
            'actif' => true,
        ]);

        Horaire::factory()->count(2)->create([
            'paroisse_id' => $paroisse->id,
            'actif' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $stats = $response->viewData('page')['props']['stats'];

        $this->assertEquals(3, $stats['horaires_actifs']);
    }

    #[Test]
    public function dashboard_uses_efficient_queries()
    {
        $paroisse = Paroisse::factory()->create();
        $admin = User::factory()->admin()->create(['paroisse_id' => $paroisse->id]);

        Annonce::factory()->count(5)->create(['paroisse_id' => $paroisse->id]);
        Horaire::factory()->count(5)->create(['paroisse_id' => $paroisse->id]);

        $this->actingAs($admin);

        // Le dashboard doit être efficace : 1 requête agrégée pour les annonces
        \DB::enableQueryLog();
        $this->get(route('admin.dashboard'));
        $queries = \DB::getQueryLog();

        // Compter les requêtes sur la table annonces
        $annonceQueries = collect($queries)->filter(function ($query) {
            return str_contains($query['query'], 'annonces');
        });

        // Doit être une seule requête avec SELECT COUNT et SUM
        $this->assertCount(1, $annonceQueries);
        $this->assertStringContainsString('COUNT(*)', $annonceQueries->first()['query']);
    }

    #[Test]
    public function dashboard_does_not_show_other_paroisses_data()
    {
        $paroisse1 = Paroisse::factory()->create();
        $paroisse2 = Paroisse::factory()->create();

        $admin1 = User::factory()->admin()->create(['paroisse_id' => $paroisse1->id]);

        // Annonces de paroisse1
        Annonce::factory()->create(['paroisse_id' => $paroisse1->id, 'publie_le' => Carbon::now()->subDay()]);

        // Annonces de paroisse2
        Annonce::factory()->count(3)->create(['paroisse_id' => $paroisse2->id, 'publie_le' => Carbon::now()->subDay()]);

        $response = $this->actingAs($admin1)->get(route('admin.dashboard'));

        $stats = $response->viewData('page')['props']['stats'];

        // Ne voit que ses propres annonces
        $this->assertEquals(1, $stats['annonces_publiees']);
    }
}
