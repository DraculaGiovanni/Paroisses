<?php

namespace Tests\Feature;

use App\Models\Annonce;
use App\Models\CouleurExceptionnelle;
use App\Models\Horaire;
use App\Models\Paroisse;
use App\Models\User;
use App\Support\AnnouncementCategory;
use App\Support\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class Phase1PublicAndAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_announcement_statuses_cover_draft_scheduled_published_expired_and_archived(): void
    {
        $this->travelTo('2026-11-18 10:00:00');
        $paroisse = Paroisse::factory()->create();
        $draft = Annonce::factory()->draft()->create(['paroisse_id' => $paroisse->id]);
        $scheduled = Annonce::factory()->create([
            'paroisse_id' => $paroisse->id,
            'publie_le' => now()->addHour(),
        ]);
        $published = Annonce::factory()->create([
            'paroisse_id' => $paroisse->id,
            'publie_le' => now()->subHour(),
            'expire_le' => now()->addHour(),
        ]);
        $expired = Annonce::factory()->create([
            'paroisse_id' => $paroisse->id,
            'publie_le' => now()->subDay(),
            'expire_le' => now()->subHour(),
        ]);
        $archived = Annonce::factory()->create([
            'paroisse_id' => $paroisse->id,
            'publie_le' => now()->subHour(),
            'archivee_le' => now(),
        ]);

        $this->assertSame('brouillon', $draft->status);
        $this->assertSame('programmee', $scheduled->status);
        $this->assertSame('publiee', $published->status);
        $this->assertSame('expiree', $expired->status);
        $this->assertSame('archivee', $archived->status);
    }

    public function test_scheduled_announcements_become_visible_when_their_time_passes_without_cache_invalidation(): void
    {
        $this->travelTo('2026-11-18 10:00:00');
        $paroisse = Paroisse::factory()->create();
        $scheduled = Annonce::factory()->create([
            'paroisse_id' => $paroisse->id,
            'titre' => 'Annonce programmée',
            'publie_le' => now()->addHour(),
            'actif' => true,
        ]);
        Cache::forget("paroisse:{$paroisse->id}:public:v3");

        $first = $this->get(route('paroisse.show', $paroisse));
        $first->assertOk();
        $this->assertCount(0, $first->viewData('page')['props']['annonces']);

        $this->travelTo('2026-11-18 11:01:00');
        $second = $this->get(route('paroisse.show', $paroisse));
        $second->assertOk();
        $titles = array_column($second->viewData('page')['props']['annonces'], 'titre');
        $this->assertContains('Annonce programmée', $titles);
        $this->assertModelExists($scheduled);
    }

    public function test_public_category_filter_and_detail_isolate_between_paroisses(): void
    {
        $paroisseA = Paroisse::factory()->create();
        $paroisseB = Paroisse::factory()->create();
        $annonceA = Annonce::factory()->create([
            'paroisse_id' => $paroisseA->id,
            'titre' => 'Urgence A',
            'categorie' => AnnouncementCategory::Urgence->value,
            'publie_le' => now()->subHour(),
        ]);
        Annonce::factory()->create([
            'paroisse_id' => $paroisseA->id,
            'titre' => 'Catéchèse A',
            'categorie' => AnnouncementCategory::Catechese->value,
            'publie_le' => now()->subHour(),
        ]);
        $annonceB = Annonce::factory()->create([
            'paroisse_id' => $paroisseB->id,
            'publie_le' => now()->subHour(),
        ]);

        $filtered = $this->get(route('paroisse.annonces.index', [
            'paroisse' => $paroisseA,
            'categorie' => AnnouncementCategory::Urgence->value,
        ]));
        $filtered->assertOk();
        $this->assertCount(1, $filtered->viewData('page')['props']['annonces']['data']);

        $this->get(route('paroisse.annonces.show', [
            'paroisse' => $paroisseA,
            'annonce' => $annonceA->id,
        ]))->assertOk();

        $this->get(route('paroisse.annonces.show', [
            'paroisse' => $paroisseA,
            'annonce' => $annonceB->id,
        ]))->assertNotFound();
    }

    public function test_admin_schedule_validation_accepts_ranges_and_rejects_invalid_ranges(): void
    {
        $paroisse = Paroisse::factory()->create();
        $admin = User::factory()->admin()->create(['paroisse_id' => $paroisse->id]);

        $this->actingAs($admin)
            ->post(route('admin.horaires.store'), [
                'type' => 'messe',
                'jour_semaine' => 7,
                'heure' => '08:00',
                'heure_fin' => '09:30',
                'valide_du' => '2026-11-01',
                'valide_au' => '2026-12-31',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('horaires', [
            'paroisse_id' => $paroisse->id,
            'heure' => '08:00',
            'heure_fin' => '09:30',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.horaires.index'))
            ->post(route('admin.horaires.store'), [
                'type' => 'messe',
                'jour_semaine' => 7,
                'heure' => '10:00',
                'heure_fin' => '09:30',
                'valide_du' => '2026-12-31',
                'valide_au' => '2026-11-01',
            ])
            ->assertSessionHasErrors(['heure_fin', 'valide_au']);
    }

    public function test_schedules_outside_validity_are_hidden_and_next_mass_ignores_them(): void
    {
        $this->travelTo('2026-11-18 10:00:00');
        $paroisse = Paroisse::factory()->create();
        Horaire::factory()->create([
            'paroisse_id' => $paroisse->id,
            'type' => 'messe',
            'jour_semaine' => 3,
            'heure' => '11:00',
            'valide_du' => '2026-12-01',
            'actif' => true,
        ]);
        Horaire::factory()->create([
            'paroisse_id' => $paroisse->id,
            'type' => 'messe',
            'jour_semaine' => 3,
            'heure' => '12:00',
            'valide_du' => '2026-11-01',
            'valide_au' => '2026-11-30',
            'heure_fin' => '13:00',
            'actif' => true,
        ]);

        $response = $this->get(route('paroisse.show', $paroisse));
        $response->assertOk();
        $props = $response->viewData('page')['props'];
        $this->assertSame('12:00', $props['prochaine_messe']['heure']);
        $this->assertCount(1, $props['horaires']);
    }

    public function test_exceptional_liturgy_color_is_prioritized_and_permission_is_enforced(): void
    {
        $this->travelTo('2026-11-18 10:00:00');
        $paroisse = Paroisse::factory()->create();
        CouleurExceptionnelle::create([
            'paroisse_id' => $paroisse->id,
            'date' => '2026-11-18',
            'couleur' => 'rouge',
            'libelle' => 'Fête locale',
        ]);
        $this->assertDatabaseHas('couleurs_exceptionnelles', [
            'paroisse_id' => $paroisse->id,
            'couleur' => 'rouge',
            'libelle' => 'Fête locale',
        ]);
        $this->assertNotNull(CouleurExceptionnelle::forParoisse($paroisse->id)
            ->whereDate('date', '2026-11-18')
            ->first());

        $response = $this->get(route('paroisse.show', $paroisse));
        $liturgical = $response->viewData('page')['props']['liturgical'];
        $this->assertSame('rouge', $liturgical['color']['key']);
        $this->assertSame('Fête locale', $liturgical['period']['label']);

        $secretariat = User::factory()->create([
            'paroisse_id' => $paroisse->id,
            'permissions' => [],
        ]);
        $this->actingAs($secretariat)
            ->get(route('admin.liturgie.index'))
            ->assertForbidden();

        $secretariat->update(['permissions' => [Permission::LiturgieGerer->value]]);
        $this->actingAs($secretariat)
            ->get(route('admin.liturgie.index'))
            ->assertOk();
    }
}
