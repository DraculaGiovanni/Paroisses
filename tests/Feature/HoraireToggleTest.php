<?php

namespace Tests\Feature;

use App\Models\Horaire;
use App\Models\Paroisse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HoraireToggleTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function admin_can_toggle_their_own_horaire()
    {
        $paroisse = Paroisse::factory()->create();
        $admin = User::factory()->admin()->create(['paroisse_id' => $paroisse->id]);
        $horaire = Horaire::factory()->create([
            'paroisse_id' => $paroisse->id,
            'actif' => true,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.horaires.update', $horaire->id), [
                'type' => $horaire->type,
                'jour_semaine' => $horaire->jour_semaine,
                'heure' => $horaire->heure->format('H:i'),
                'lieu' => $horaire->lieu,
                'note' => $horaire->note,
                'actif' => false,
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('horaires', [
            'id' => $horaire->id,
            'actif' => false,
        ]);
    }

    #[Test]
    public function admin_cannot_toggle_another_paroisse_horaire()
    {
        $paroisse1 = Paroisse::factory()->create();
        $paroisse2 = Paroisse::factory()->create();

        $admin1 = User::factory()->admin()->create(['paroisse_id' => $paroisse1->id]);
        $horaire2 = Horaire::factory()->create([
            'paroisse_id' => $paroisse2->id,
            'actif' => true,
        ]);

        $this->actingAs($admin1)
            ->put(route('admin.horaires.update', $horaire2->id), [
                'type' => $horaire2->type,
                'jour_semaine' => $horaire2->jour_semaine,
                'heure' => $horaire2->heure->format('H:i'),
                'lieu' => $horaire2->lieu,
                'note' => $horaire2->note,
                'actif' => false,
            ])
            ->assertNotFound(); // 404 car pas dans son scope
    }

    #[Test]
    public function guest_cannot_toggle_horaire()
    {
        $paroisse = Paroisse::factory()->create();
        $horaire = Horaire::factory()->create(['paroisse_id' => $paroisse->id]);

        $this->put(route('admin.horaires.update', $horaire->id), [
            'actif' => false,
        ])
            ->assertRedirect(route('login'));
    }
}
