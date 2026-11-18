<?php

namespace Tests\Feature;

use App\Models\Horaire;
use App\Models\IntentionMesse;
use App\Models\Paroisse;
use App\Models\User;
use App\Support\IntentionCode;
use App\Support\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Log\Logger as LaravelLogger;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia;
use Monolog\Handler\TestHandler;
use Monolog\Logger as MonologLogger;
use Tests\TestCase;

class Phase2AdminIntentionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-11-18 10:00:00', 'Africa/Porto-Novo'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_valid_transitions_are_applied_in_order(): void
    {
        $paroisse = Paroisse::factory()->create();
        $admin = $this->admin($paroisse);
        $messe = Horaire::factory()->create([
            'paroisse_id' => $paroisse->id,
            'type' => 'messe',
            'jour_semaine' => 6,
            'heure' => '18:00',
            'lieu' => 'Cathédrale',
        ]);
        $intention = $this->creerIntention($paroisse);

        $this->patchStatut($admin, $intention, ['statut' => 'confirmee'])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $intention->refresh();
        $this->assertSame('confirmee', $intention->statut);
        $this->assertSame($admin->id, $intention->traitee_par);

        $this->patchStatut($admin, $intention, [
            'statut' => 'programmee',
            'date_programmee' => '2026-11-21',
            'horaire_programme_id' => $messe->id,
        ])->assertSessionHasNoErrors();

        $intention->refresh();
        $this->assertSame('programmee', $intention->statut);
        $this->assertSame('2026-11-21', $intention->date_programmee?->toDateString());
        $this->assertSame($messe->id, $intention->horaire_programme_id);

        $this->patchStatut($admin, $intention, ['statut' => 'celebree'])
            ->assertSessionHasNoErrors();

        $this->assertSame('celebree', $intention->fresh()->statut);

        $this->patchStatut($admin, $intention, [
            'statut' => 'refusee',
            'motif_refus' => 'Trop tard.',
        ])->assertSessionHasErrors('statut');

        $this->assertSame('celebree', $intention->fresh()->statut);
    }

    public function test_invalid_transitions_are_rejected(): void
    {
        $paroisse = Paroisse::factory()->create();
        $admin = $this->admin($paroisse);

        $refusee = $this->creerIntention($paroisse, [
            'statut' => 'refusee',
            'motif_refus' => 'Non retenue.',
        ]);

        $this->patchStatut($admin, $refusee, ['statut' => 'confirmee'])
            ->assertSessionHasErrors('statut');

        $this->assertSame('refusee', $refusee->fresh()->statut);

        $celebree = $this->creerIntention($paroisse, ['statut' => 'celebree']);

        $this->patchStatut($admin, $celebree, [
            'statut' => 'refusee',
            'motif_refus' => 'Trop tard.',
        ])->assertSessionHasErrors('statut');

        $this->patchStatut($admin, $celebree, ['statut' => 'annulee'])
            ->assertSessionHasErrors('statut');

        $this->assertSame('celebree', $celebree->fresh()->statut);
    }

    public function test_programming_requires_a_date_and_a_mass_of_the_same_parish(): void
    {
        $paroisse = Paroisse::factory()->create();
        $autreParoisse = Paroisse::factory()->create();
        $admin = $this->admin($paroisse);
        $messe = Horaire::factory()->create([
            'paroisse_id' => $paroisse->id,
            'type' => 'messe',
        ]);
        $messeEtrangere = Horaire::factory()->create([
            'paroisse_id' => $autreParoisse->id,
            'type' => 'messe',
        ]);
        $intention = $this->creerIntention($paroisse);

        $this->patchStatut($admin, $intention, [
            'statut' => 'programmee',
            'date_programmee' => '2026-11-21',
            'horaire_programme_id' => $messeEtrangere->id,
        ])->assertSessionHasErrors('horaire_programme_id');

        $this->patchStatut($admin, $intention, [
            'statut' => 'programmee',
            'horaire_programme_id' => $messe->id,
        ])->assertSessionHasErrors('date_programmee');

        $this->patchStatut($admin, $intention, [
            'statut' => 'programmee',
            'date_programmee' => '2026-11-17',
            'horaire_programme_id' => $messe->id,
        ])->assertSessionHasErrors('date_programmee');

        $this->assertSame('recue', $intention->fresh()->statut);

        $this->patchStatut($admin, $intention, [
            'statut' => 'programmee',
            'date_programmee' => '2026-11-21',
            'horaire_programme_id' => $messe->id,
        ])->assertSessionHasNoErrors();

        $intention->refresh();
        $this->assertSame('programmee', $intention->statut);
        $this->assertSame($messe->id, $intention->horaire_programme_id);
    }

    public function test_refusal_requires_a_reason_of_at_most_two_hundred_characters(): void
    {
        $paroisse = Paroisse::factory()->create();
        $admin = $this->admin($paroisse);
        $intention = $this->creerIntention($paroisse);

        $this->patchStatut($admin, $intention, ['statut' => 'refusee'])
            ->assertSessionHasErrors('motif_refus');

        $this->patchStatut($admin, $intention, [
            'statut' => 'refusee',
            'motif_refus' => str_repeat('a', 201),
        ])->assertSessionHasErrors('motif_refus');

        $this->assertSame('recue', $intention->fresh()->statut);

        $this->patchStatut($admin, $intention, [
            'statut' => 'refusee',
            'motif_refus' => 'La date est déjà prise.',
        ])->assertSessionHasNoErrors();

        $intention->refresh();
        $this->assertSame('refusee', $intention->statut);
        $this->assertSame('La date est déjà prise.', $intention->motif_refus);
        $this->assertSame($admin->id, $intention->traitee_par);
    }

    public function test_index_filters_by_status_and_date_and_isolates_parishes(): void
    {
        $paroisseA = Paroisse::factory()->create();
        $paroisseB = Paroisse::factory()->create();
        $admin = $this->admin($paroisseA);

        $recue = $this->creerIntention($paroisseA, [
            'nom_demandeur' => 'Recue A',
            'date_souhaitee' => '2026-11-20',
        ]);
        $programmee = $this->creerIntention($paroisseA, [
            'nom_demandeur' => 'Programmee A',
            'date_souhaitee' => '2026-11-21',
            'statut' => 'programmee',
            'date_programmee' => '2026-11-21',
        ]);
        $this->creerIntention($paroisseB, ['nom_demandeur' => 'B']);

        $response = $this->actingAs($admin)->get(route('admin.intentions.index'));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Admin/Intentions/Index', false)
            ->has('intentions.data', 2)
            ->where('nouvelles', 1)
            ->etc());

        $props = $this->pageProps($response);
        $codes = array_column($props['intentions']['data'], 'code_suivi');
        $attendus = [$programmee->code_suivi, $recue->code_suivi];
        sort($codes);
        sort($attendus);
        $this->assertSame($attendus, $codes);

        $filteredByStatus = $this->actingAs($admin)->get(route('admin.intentions.index', [
            'statut' => 'recue',
        ]));
        $filteredByStatus->assertOk();
        $this->assertCount(1, $this->pageProps($filteredByStatus)['intentions']['data']);
        $this->assertSame($recue->code_suivi, $this->pageProps($filteredByStatus)['intentions']['data'][0]['code_suivi']);

        $filteredByDate = $this->actingAs($admin)->get(route('admin.intentions.index', [
            'date' => '2026-11-21',
        ]));
        $filteredByDate->assertOk();
        $this->assertCount(1, $this->pageProps($filteredByDate)['intentions']['data']);
        $this->assertSame($programmee->code_suivi, $this->pageProps($filteredByDate)['intentions']['data'][0]['code_suivi']);
    }

    public function test_secretary_without_permission_is_forbidden(): void
    {
        $paroisse = Paroisse::factory()->create();
        $secretaire = User::factory()->create([
            'paroisse_id' => $paroisse->id,
            'permissions' => [Permission::AnnoncesGerer->value],
        ]);
        $intention = $this->creerIntention($paroisse);

        $this->actingAs($secretaire)->get(route('admin.intentions.index'))->assertForbidden();
        $this->actingAs($secretaire)->get(route('admin.intentions.show', $intention->id))->assertForbidden();
        $this->actingAs($secretaire)->get(route('admin.intentions.feuille'))->assertForbidden();
        $this->patchStatut($secretaire, $intention, ['statut' => 'confirmee'])->assertForbidden();

        $avecPermission = User::factory()->create([
            'paroisse_id' => $paroisse->id,
            'permissions' => [Permission::IntentionsGerer->value],
        ]);

        $this->actingAs($avecPermission)->get(route('admin.intentions.index'))->assertOk();
        $this->actingAs($avecPermission)->get(route('admin.intentions.show', $intention->id))->assertOk();
    }

    public function test_admin_cannot_view_or_update_an_intention_of_another_parish(): void
    {
        $paroisseA = Paroisse::factory()->create();
        $paroisseB = Paroisse::factory()->create();
        $adminA = $this->admin($paroisseA);
        $intentionB = $this->creerIntention($paroisseB, ['nom_demandeur' => 'B']);

        $this->actingAs($adminA)
            ->get(route('admin.intentions.show', $intentionB->id))
            ->assertNotFound();

        $this->patchStatut($adminA, $intentionB, ['statut' => 'confirmee'])
            ->assertNotFound();

        $this->assertSame('recue', $intentionB->fresh()->statut);
    }

    public function test_super_admin_can_view_intentions_of_any_parish(): void
    {
        $paroisseA = Paroisse::factory()->create();
        $paroisseB = Paroisse::factory()->create();
        $superAdmin = User::factory()->superAdmin()->create();
        $this->creerIntention($paroisseA, ['nom_demandeur' => 'A']);
        $intentionB = $this->creerIntention($paroisseB, ['nom_demandeur' => 'B']);

        $this->actingAs($superAdmin)->get(route('admin.intentions.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('intentions.data', 2));

        $this->actingAs($superAdmin)->get(route('admin.intentions.show', $intentionB->id))
            ->assertOk();
    }

    public function test_whatsapp_link_is_built_only_when_a_phone_is_present(): void
    {
        $paroisse = Paroisse::factory()->create();
        $admin = $this->admin($paroisse);

        $avecTelephone = $this->creerIntention($paroisse, [
            'nom_demandeur' => 'Jean Dupont',
            'telephone' => '+229 01 23 45 67 89',
        ]);

        $props = $this->pageProps(
            $this->actingAs($admin)->get(route('admin.intentions.show', $avecTelephone->id))
        );

        $this->assertIsString($props['whatsapp_url']);
        $this->assertStringStartsWith('https://wa.me/2290123456789?text=', $props['whatsapp_url']);
        $this->assertStringContainsString(rawurlencode($avecTelephone->code_suivi), $props['whatsapp_url']);

        $sansTelephone = $this->creerIntention($paroisse, ['telephone' => null]);

        $props = $this->pageProps(
            $this->actingAs($admin)->get(route('admin.intentions.show', $sansTelephone->id))
        );

        $this->assertNull($props['whatsapp_url']);
    }

    public function test_printable_sheet_groups_programmed_intentions_by_mass_and_date(): void
    {
        $paroisse = Paroisse::factory()->create();
        $admin = $this->admin($paroisse);

        $messeMatin = Horaire::factory()->create([
            'paroisse_id' => $paroisse->id,
            'type' => 'messe',
            'jour_semaine' => 6,
            'heure' => '07:00',
            'lieu' => 'Chapelle',
        ]);
        $messeSoir = Horaire::factory()->create([
            'paroisse_id' => $paroisse->id,
            'type' => 'messe',
            'jour_semaine' => 6,
            'heure' => '18:00',
            'lieu' => 'Cathédrale',
        ]);

        $matin = $this->creerIntention($paroisse, [
            'nom_demandeur' => 'Matin Un',
            'statut' => 'programmee',
            'date_programmee' => '2026-11-21',
            'horaire_programme_id' => $messeMatin->id,
        ]);
        $soir = $this->creerIntention($paroisse, [
            'nom_demandeur' => 'Soir Un',
            'statut' => 'programmee',
            'date_programmee' => '2026-11-21',
            'horaire_programme_id' => $messeSoir->id,
        ]);
        $autreDate = $this->creerIntention($paroisse, [
            'nom_demandeur' => 'Autre Date',
            'statut' => 'programmee',
            'date_programmee' => '2026-11-22',
            'horaire_programme_id' => $messeMatin->id,
        ]);
        $nonProgrammee = $this->creerIntention($paroisse, ['nom_demandeur' => 'Non Programmee']);
        $etrangere = $this->creerIntention(Paroisse::factory()->create(), [
            'nom_demandeur' => 'Autre Paroisse',
            'statut' => 'programmee',
            'date_programmee' => '2026-11-21',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.intentions.feuille', [
            'date' => '2026-11-21',
        ]));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Admin/Intentions/Feuille', false)
            ->where('date', '2026-11-21')
            ->has('groupes', 2)
            ->has('groupes.0.intentions', 1)
            ->has('groupes.1.intentions', 1)
            ->etc());

        $props = $this->pageProps($response);
        $this->assertStringContainsString('07:00', $props['groupes'][0]['horaire']);
        $this->assertStringContainsString('18:00', $props['groupes'][1]['horaire']);
        $this->assertSame($matin->code_suivi, $props['groupes'][0]['intentions'][0]['code_suivi']);
        $this->assertSame($soir->code_suivi, $props['groupes'][1]['intentions'][0]['code_suivi']);

        foreach ([$autreDate->code_suivi, $nonProgrammee->code_suivi, $etrangere->code_suivi] as $absent) {
            $this->assertStringNotContainsString($absent, $response->getContent());
        }
    }

    public function test_purge_anonymizes_only_closed_intentions_older_than_ninety_days(): void
    {
        $paroisse = Paroisse::factory()->create();

        $celebree = $this->creerIntention($paroisse, [
            'nom_demandeur' => 'Ancien Celebre',
            'telephone' => '+229 01 10 20 30 40',
            'statut' => 'celebree',
        ]);
        $refusee = $this->creerIntention($paroisse, [
            'nom_demandeur' => 'Ancien Refuse',
            'telephone' => '+229 01 11 21 31 41',
            'statut' => 'refusee',
            'motif_refus' => 'Non retenue.',
        ]);
        $annulee = $this->creerIntention($paroisse, [
            'nom_demandeur' => 'Ancien Annule',
            'telephone' => '+229 01 12 22 32 42',
            'statut' => 'annulee',
        ]);

        $this->vieillir($celebree, 91);
        $this->vieillir($refusee, 120);
        $this->vieillir($annulee, 365);

        $recente = $this->creerIntention($paroisse, [
            'nom_demandeur' => 'Recente Celebre',
            'telephone' => '+229 01 13 23 33 43',
            'statut' => 'celebree',
        ]);
        $this->vieillir($recente, 10);

        $recueAncienne = $this->creerIntention($paroisse, [
            'nom_demandeur' => 'Ancienne Recue',
            'telephone' => '+229 01 14 24 34 44',
            'statut' => 'recue',
        ]);
        $programmeeAncienne = $this->creerIntention($paroisse, [
            'nom_demandeur' => 'Ancienne Programmee',
            'telephone' => '+229 01 15 25 35 45',
            'statut' => 'programmee',
        ]);
        $this->vieillir($recueAncienne, 200);
        $this->vieillir($programmeeAncienne, 200);

        $this->artisan('intentions:purger')->assertSuccessful();

        foreach ([$celebree, $refusee, $annulee] as $intention) {
            $intention->refresh();
            $this->assertSame(IntentionMesse::NOM_ANONYME, $intention->nom_demandeur);
            $this->assertNull($intention->telephone);
        }

        $this->assertSame('celebree', $celebree->fresh()->statut);
        $this->assertSame('refusee', $refusee->fresh()->statut);
        $this->assertSame('annulee', $annulee->fresh()->statut);
        $this->assertSame('defunt', $celebree->fresh()->type);

        foreach ([$recente, $recueAncienne, $programmeeAncienne] as $intention) {
            $intention->refresh();
            $this->assertNotSame(IntentionMesse::NOM_ANONYME, $intention->nom_demandeur);
            $this->assertNotNull($intention->telephone);
        }

        $this->artisan('intentions:purger')
            ->expectsOutput('0 demande(s) anonymisée(s).')
            ->assertSuccessful();
    }

    public function test_personal_data_is_never_written_to_logs(): void
    {
        $handler = new TestHandler;
        Log::swap(new LaravelLogger(new MonologLogger('testing', [$handler])));

        $paroisse = Paroisse::factory()->create();
        $admin = $this->admin($paroisse);

        $intention = $this->creerIntention($paroisse, [
            'nom_demandeur' => 'Nom Secret Log',
            'telephone' => '+229 01 11 22 33 44',
            'intention' => 'Intention secrète à ne pas journaliser.',
        ]);

        $this->patchStatut($admin, $intention, [
            'statut' => 'refusee',
            'motif_refus' => 'Motif visible uniquement dans l\'administration.',
        ])->assertSessionHasNoErrors();

        $ancienne = $this->creerIntention($paroisse, [
            'nom_demandeur' => 'Ancien Nom Secret',
            'telephone' => '+229 01 55 66 77 88',
            'intention' => 'Ancienne intention secrète.',
            'statut' => 'refusee',
            'motif_refus' => 'Refus.',
        ]);
        $this->vieillir($ancienne, 120);

        $this->artisan('intentions:purger')->assertSuccessful();

        $secrets = [
            'Nom Secret Log',
            '+229 01 11 22 33 44',
            'Intention secrète à ne pas journaliser.',
            'Ancien Nom Secret',
            '+229 01 55 66 77 88',
            'Ancienne intention secrète.',
        ];

        foreach ($handler->getRecords() as $record) {
            $contenu = $record['message'].' '.json_encode($record['context'], JSON_UNESCAPED_UNICODE);

            foreach ($secrets as $secret) {
                $this->assertStringNotContainsString($secret, $contenu);
            }
        }
    }

    public function test_menu_counter_is_shared_only_with_authorized_users(): void
    {
        $paroisse = Paroisse::factory()->create();
        $admin = $this->admin($paroisse);

        $this->creerIntention($paroisse);
        $this->creerIntention($paroisse);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $this->assertSame(2, $this->pageProps($response)['menu']['intentions_nouvelles']);

        $sansPermission = User::factory()->create([
            'paroisse_id' => $paroisse->id,
            'permissions' => [Permission::AnnoncesGerer->value],
        ]);
        $response = $this->actingAs($sansPermission)->get(route('admin.dashboard'));

        $response->assertOk();
        $this->assertSame(0, $this->pageProps($response)['menu']['intentions_nouvelles']);
    }

    private function admin(Paroisse $paroisse): User
    {
        return User::factory()->admin()->create(['paroisse_id' => $paroisse->id]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function creerIntention(Paroisse $paroisse, array $overrides = []): IntentionMesse
    {
        return IntentionMesse::create(array_merge([
            'paroisse_id' => $paroisse->id,
            'code_suivi' => IntentionCode::generate(),
            'nom_demandeur' => 'Jean Dupont',
            'telephone' => '+229 01 23 45 67 89',
            'type' => 'defunt',
            'intention' => 'Pour le repos de l\'âme de Marie.',
            'date_souhaitee' => '2026-11-20',
            'statut' => 'recue',
        ], $overrides));
    }

    private function patchStatut(User $user, IntentionMesse $intention, array $payload): TestResponse
    {
        return $this->actingAs($user)
            ->from(route('admin.intentions.show', $intention->id))
            ->patch(route('admin.intentions.statut', $intention->id), $payload);
    }

    private function vieillir(IntentionMesse $intention, int $jours): void
    {
        $intention->timestamps = false;
        $intention->forceFill(['updated_at' => now()->subDays($jours)])->save();
        $intention->timestamps = true;
    }

    /**
     * @return array<string, mixed>
     */
    private function pageProps(TestResponse $response): array
    {
        $props = [];

        $response->assertInertia(function (AssertableInertia $page) use (&$props): void {
            $props = $page->toArray()['props'];
        });

        return $props;
    }
}
