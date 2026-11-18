<?php

namespace Tests\Feature;

use App\Models\Annonce;
use App\Models\Horaire;
use App\Models\Paroisse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BelongsToParoisseSecurityTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_without_paroisse_sees_no_data_fail_closed()
    {
        $paroisse = Paroisse::factory()->create();
        Annonce::factory()->count(3)->create(['paroisse_id' => $paroisse->id]);

        // Utilisateur sans paroisse_id (orphelin)
        $userWithoutParoisse = User::factory()->create([
            'paroisse_id' => null,
            'role' => 'admin', // même admin
        ]);

        $this->actingAs($userWithoutParoisse);

        // Ne doit voir AUCUNE annonce (fail closed)
        $this->assertCount(0, Annonce::all());
        $this->assertCount(0, Horaire::all());
    }

    #[Test]
    public function super_admin_sees_all_data()
    {
        $paroisse1 = Paroisse::factory()->create();
        $paroisse2 = Paroisse::factory()->create();

        Annonce::factory()->create(['paroisse_id' => $paroisse1->id]);
        Annonce::factory()->create(['paroisse_id' => $paroisse2->id]);

        $super = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($super);

        // Voit toutes les annonces
        $this->assertCount(2, Annonce::all());
    }

    #[Test]
    public function admin_sees_only_their_paroisse_data()
    {
        $paroisse1 = Paroisse::factory()->create();
        $paroisse2 = Paroisse::factory()->create();

        Annonce::factory()->create(['paroisse_id' => $paroisse1->id]);
        Annonce::factory()->create(['paroisse_id' => $paroisse2->id]);

        $admin1 = User::factory()->admin()->create(['paroisse_id' => $paroisse1->id]);
        $this->actingAs($admin1);

        // Ne voit que sa paroisse
        $this->assertCount(1, Annonce::all());
        $this->assertEquals($paroisse1->id, Annonce::first()->paroisse_id);
    }

    #[Test]
    public function guest_queries_are_not_automatically_filtered()
    {
        $paroisseA = Paroisse::factory()->create();
        $paroisseB = Paroisse::factory()->create();
        Annonce::factory()->create(['paroisse_id' => $paroisseA->id]);
        Annonce::factory()->create(['paroisse_id' => $paroisseB->id]);
        Horaire::factory()->create(['paroisse_id' => $paroisseA->id]);
        Horaire::factory()->create(['paroisse_id' => $paroisseB->id]);

        $this->assertCount(2, Annonce::all());
        $this->assertCount(2, Horaire::all());
    }

    #[Test]
    public function user_without_paroisse_cannot_create_data()
    {
        $userWithoutParoisse = User::factory()->create([
            'paroisse_id' => null,
            'role' => 'admin',
        ]);

        $this->actingAs($userWithoutParoisse);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('utilisateur n\'est rattaché à aucune paroisse');

        Annonce::create([
            'titre' => 'Test',
            'contenu' => 'Contenu',
        ]);
    }

    #[Test]
    public function admin_cannot_create_data_for_another_paroisse()
    {
        $paroisse1 = Paroisse::factory()->create();
        $paroisse2 = Paroisse::factory()->create();

        $admin1 = User::factory()->admin()->create(['paroisse_id' => $paroisse1->id]);
        $this->actingAs($admin1);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('ne correspond pas à celle de l\'utilisateur');

        Annonce::create([
            'paroisse_id' => $paroisse2->id,
            'titre' => 'Test',
            'contenu' => 'Contenu',
        ]);
    }

    #[Test]
    public function super_admin_can_create_for_any_paroisse()
    {
        $paroisse = Paroisse::factory()->create();
        $super = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($super);

        $annonce = Annonce::create([
            'paroisse_id' => $paroisse->id,
            'titre' => 'Test',
            'contenu' => 'Contenu',
        ]);

        // Super admin peut créer pour n'importe quelle paroisse
        $this->assertEquals($paroisse->id, $annonce->paroisse_id);
    }

    #[Test]
    public function console_can_create_for_a_paroisse_only_when_its_id_is_explicit()
    {
        $paroisse = Paroisse::factory()->create();

        Annonce::create([
            'paroisse_id' => $paroisse->id,
            'titre' => 'Créée sans utilisateur',
            'contenu' => 'Contenu',
        ]);

        $this->assertDatabaseHas('annonces', [
            'paroisse_id' => $paroisse->id,
            'titre' => 'Créée sans utilisateur',
        ]);
    }

    #[Test]
    public function console_cannot_create_without_an_explicit_paroisse_id()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('une paroisse_id explicite est requise hors authentification');

        Annonce::create([
            'titre' => 'Sans paroisse',
            'contenu' => 'Contenu',
        ]);
    }

    #[Test]
    public function for_paroisse_can_explicitly_read_another_paroisse()
    {
        $paroisseA = Paroisse::factory()->create();
        $paroisseB = Paroisse::factory()->create();
        Annonce::factory()->create(['paroisse_id' => $paroisseA->id]);
        $annonceB = Annonce::factory()->create(['paroisse_id' => $paroisseB->id]);
        $adminA = User::factory()->admin()->create(['paroisse_id' => $paroisseA->id]);

        $this->actingAs($adminA);

        $this->assertSame(
            [$annonceB->id],
            Annonce::forParoisse($paroisseB->id)->pluck('id')->all()
        );
    }

    #[Test]
    public function horaires_follow_same_isolation_rules()
    {
        $paroisse1 = Paroisse::factory()->create();
        $paroisse2 = Paroisse::factory()->create();

        Horaire::factory()->create(['paroisse_id' => $paroisse1->id]);
        Horaire::factory()->create(['paroisse_id' => $paroisse2->id]);

        $admin1 = User::factory()->admin()->create(['paroisse_id' => $paroisse1->id]);
        $this->actingAs($admin1);

        // Ne voit que ses horaires
        $this->assertCount(1, Horaire::all());
        $this->assertEquals($paroisse1->id, Horaire::first()->paroisse_id);
    }
}
