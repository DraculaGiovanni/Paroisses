<?php

namespace Tests\Feature;

use App\Http\Requests\Public\StoreIntentionRequest;
use App\Http\Requests\Public\SuivreIntentionRequest;
use App\Models\Horaire;
use App\Models\IntentionMesse;
use App\Models\Paroisse;
use App\Support\IntentionCode;
use App\Support\PublicFormToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia;
use RuntimeException;
use Tests\TestCase;

class Phase2IntentionsPubliquesTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_intention_code_contains_ten_allowed_characters(): void
    {
        for ($index = 0; $index < 100; $index++) {
            $code = IntentionCode::generate();

            $this->assertSame(10, strlen($code));
            $this->assertMatchesRegularExpression('/^[23456789ABCDEFGHJKMNPQRSTUVWXYZ]{10}$/', $code);
            $this->assertTrue(IntentionCode::isValid($code));
        }
    }

    public function test_intention_code_is_not_sequential_over_one_thousand_codes(): void
    {
        $codes = [];

        for ($index = 0; $index < 1000; $index++) {
            $codes[] = IntentionCode::generate();
        }

        $this->assertCount(1000, array_unique($codes));

        $sortedCodes = $codes;
        sort($sortedCodes, SORT_STRING);

        $this->assertNotSame($sortedCodes, $codes);
    }

    public function test_intention_code_retries_when_a_collision_is_simulated(): void
    {
        $attempts = 0;

        $code = IntentionCode::generateUnique(function (string $candidate) use (&$attempts): bool {
            $attempts++;

            return $attempts <= 2;
        });

        $this->assertSame(3, $attempts);
        $this->assertTrue(IntentionCode::isValid($code));
    }

    public function test_intention_code_fails_after_too_many_collisions(): void
    {
        $this->assertThrows(
            fn () => IntentionCode::generateUnique(fn (string $candidate): bool => true, 3),
            RuntimeException::class
        );
    }

    public function test_public_form_token_is_accepted_at_three_seconds(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-18 10:00:00'));
        $token = PublicFormToken::issue();

        Carbon::setTestNow(Carbon::parse('2026-11-18 10:00:03'));

        $this->assertTrue(PublicFormToken::isValid($token));
    }

    public function test_public_form_token_is_rejected_before_three_seconds(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-18 10:00:00'));
        $token = PublicFormToken::issue();

        Carbon::setTestNow(Carbon::parse('2026-11-18 10:00:02'));
        $this->assertFalse(PublicFormToken::isValid($token));

        Carbon::setTestNow(Carbon::parse('2026-11-18 10:00:00'));
        $this->assertFalse(PublicFormToken::isValid($token));
    }

    public function test_public_form_token_is_rejected_after_expiration(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-18 10:00:00'));
        $token = PublicFormToken::issue();

        Carbon::setTestNow(Carbon::parse('2026-11-18 10:00:00')->addSeconds(PublicFormToken::MAX_AGE_SECONDS));
        $this->assertTrue(PublicFormToken::isValid($token));

        Carbon::setTestNow(Carbon::parse('2026-11-18 10:00:00')->addSeconds(PublicFormToken::MAX_AGE_SECONDS + 1));
        $this->assertFalse(PublicFormToken::isValid($token));
    }

    public function test_public_form_token_is_rejected_when_signature_is_modified(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-18 10:00:00'));
        $token = PublicFormToken::issue();
        [$timestamp, $signature] = explode('.', $token);
        $signature[0] = $signature[0] === 'a' ? 'b' : 'a';

        Carbon::setTestNow(Carbon::parse('2026-11-18 10:00:04'));

        $this->assertFalse(PublicFormToken::isValid($timestamp.'.'.$signature));
    }

    public function test_public_form_token_is_rejected_when_empty_or_malformed(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-18 10:00:04'));

        foreach (['', 'abc', '123', '.', '123.', '.abc', '123.abc.def', 'abc.def', str_repeat('1', 101)] as $token) {
            $this->assertFalse(
                PublicFormToken::isValid($token),
                "Le jeton [{$token}] aurait dû être refusé."
            );
        }
    }

    public function test_store_intention_request_accepts_a_valid_payload(): void
    {
        $paroisse = Paroisse::factory()->create();

        $this->assertSame([], $this->storeErrors($this->validIntentionPayload($paroisse), $paroisse));
    }

    public function test_store_intention_request_requires_mandatory_fields(): void
    {
        $paroisse = Paroisse::factory()->create();

        $errors = $this->storeErrors([], $paroisse);

        $this->assertContains('Le nom du demandeur est obligatoire.', $errors['nom_demandeur']);
        $this->assertContains('Le type de demande est obligatoire.', $errors['type']);
        $this->assertContains('L\'intention est obligatoire.', $errors['intention']);
        $this->assertArrayHasKey('form_token', $errors);
    }

    public function test_store_intention_request_enforces_maximum_lengths(): void
    {
        $paroisse = Paroisse::factory()->create();

        $errors = $this->storeErrors($this->validIntentionPayload($paroisse, [
            'nom_demandeur' => str_repeat('a', 101),
            'telephone' => str_repeat('1', 31),
            'intention' => str_repeat('a', 301),
        ]), $paroisse);

        $this->assertArrayHasKey('nom_demandeur', $errors);
        $this->assertArrayHasKey('telephone', $errors);
        $this->assertArrayHasKey('intention', $errors);
    }

    public function test_store_intention_request_rejects_invalid_phone_and_type(): void
    {
        $paroisse = Paroisse::factory()->create();

        $errors = $this->storeErrors($this->validIntentionPayload($paroisse, [
            'telephone' => 'pas-un-numero',
            'type' => 'inconnu',
        ]), $paroisse);

        $this->assertArrayHasKey('telephone', $errors);
        $this->assertArrayHasKey('type', $errors);
    }

    public function test_store_intention_request_uses_porto_novo_for_today(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-18 23:30:00', 'UTC'));

        $paroisse = Paroisse::factory()->create();

        $this->assertSame([], $this->storeErrors(
            $this->validIntentionPayload($paroisse, ['date_souhaitee' => '2026-11-19']),
            $paroisse
        ));

        $errors = $this->storeErrors(
            $this->validIntentionPayload($paroisse, ['date_souhaitee' => '2026-11-18']),
            $paroisse
        );
        $this->assertArrayHasKey('date_souhaitee', $errors);

        $errors = $this->storeErrors(
            $this->validIntentionPayload($paroisse, ['date_souhaitee' => '25/12/2026']),
            $paroisse
        );
        $this->assertArrayHasKey('date_souhaitee', $errors);
    }

    public function test_store_intention_request_restricts_horaire_to_a_mass_of_the_current_paroisse(): void
    {
        $paroisseA = Paroisse::factory()->create();
        $paroisseB = Paroisse::factory()->create();
        $messeA = Horaire::factory()->create(['paroisse_id' => $paroisseA->id, 'type' => 'messe']);
        $messeB = Horaire::factory()->create(['paroisse_id' => $paroisseB->id, 'type' => 'messe']);
        $confessionA = Horaire::factory()->create(['paroisse_id' => $paroisseA->id, 'type' => 'confession']);

        $this->assertSame([], $this->storeErrors(
            $this->validIntentionPayload($paroisseA, ['horaire_id' => $messeA->id]),
            $paroisseA
        ));

        $this->assertArrayHasKey('horaire_id', $this->storeErrors(
            $this->validIntentionPayload($paroisseA, ['horaire_id' => $messeB->id]),
            $paroisseA
        ));

        $this->assertArrayHasKey('horaire_id', $this->storeErrors(
            $this->validIntentionPayload($paroisseA, ['horaire_id' => $confessionA->id]),
            $paroisseA
        ));
    }

    public function test_store_intention_request_rejects_a_filled_honeypot(): void
    {
        $paroisse = Paroisse::factory()->create();

        $errors = $this->storeErrors(
            $this->validIntentionPayload($paroisse, ['reference_colis' => 'spam']),
            $paroisse
        );

        $this->assertContains('Ce champ doit rester vide.', $errors['reference_colis']);
    }

    public function test_store_intention_request_rejects_missing_expired_or_tampered_token(): void
    {
        $paroisse = Paroisse::factory()->create();

        $missingToken = $this->validIntentionPayload($paroisse, ['date_souhaitee' => null]);
        unset($missingToken['form_token']);
        $this->assertArrayHasKey('form_token', $this->storeErrors($missingToken, $paroisse));

        $expiredToken = $this->validIntentionPayload($paroisse, [
            'date_souhaitee' => null,
            'form_token' => $this->expiredToken(),
        ]);
        $this->assertArrayHasKey('form_token', $this->storeErrors($expiredToken, $paroisse));

        $tamperedToken = $this->validIntentionPayload($paroisse, [
            'date_souhaitee' => null,
            'form_token' => $this->tamperedToken(),
        ]);
        $this->assertArrayHasKey('form_token', $this->storeErrors($tamperedToken, $paroisse));
    }

    public function test_suivre_intention_request_accepts_a_valid_code_case_insensitively(): void
    {
        $this->assertSame([], $this->suiviErrors([
            'code_suivi' => 'abcdefghjk',
            'reference_colis' => '',
            'form_token' => $this->validToken(),
        ]));
    }

    public function test_suivre_intention_request_rejects_missing_or_malformed_code(): void
    {
        $this->assertArrayHasKey('code_suivi', $this->suiviErrors([
            'form_token' => $this->validToken(),
        ]));

        foreach (['ABC', 'ABCDEFGHJ0', 'ABCDEFGHI', 'ABCDEFGHJKK'] as $code) {
            $errors = $this->suiviErrors([
                'code_suivi' => $code,
                'form_token' => $this->validToken(),
            ]);

            $this->assertArrayHasKey('code_suivi', $errors, "Le code [{$code}] aurait dû être refusé.");
        }
    }

    public function test_suivre_intention_request_rejects_filled_honeypot_and_invalid_token(): void
    {
        $honeypotErrors = $this->suiviErrors([
            'code_suivi' => 'ABCDEFGHJK',
            'reference_colis' => 'spam',
            'form_token' => $this->validToken(),
        ]);
        $this->assertContains('Ce champ doit rester vide.', $honeypotErrors['reference_colis']);

        $tokenErrors = $this->suiviErrors([
            'code_suivi' => 'ABCDEFGHJK',
            'reference_colis' => '',
            'form_token' => 'invalide',
        ]);
        $this->assertArrayHasKey('form_token', $tokenErrors);
    }

    public function test_public_creation_form_exposes_only_current_parish_mass_schedules(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-18 10:00:00', 'Africa/Porto-Novo'));

        $paroisse = Paroisse::factory()->create(['slug' => 'saint-michel']);
        $autreParoisse = Paroisse::factory()->create();

        $messe = Horaire::factory()->create([
            'paroisse_id' => $paroisse->id,
            'type' => 'messe',
            'jour_semaine' => 7,
            'heure' => '09:00',
            'lieu' => 'Cathédrale',
            'actif' => true,
        ]);

        Horaire::factory()->create([
            'paroisse_id' => $paroisse->id,
            'type' => 'confession',
            'actif' => true,
        ]);

        Horaire::factory()->create([
            'paroisse_id' => $paroisse->id,
            'type' => 'messe',
            'actif' => false,
        ]);

        Horaire::factory()->create([
            'paroisse_id' => $autreParoisse->id,
            'type' => 'messe',
            'actif' => true,
        ]);

        $response = $this->get(route('paroisse.intentions.nouvelle', $paroisse));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Public/Intentions/Nouvelle', false)
            ->where('paroisse.slug', 'saint-michel')
            ->where('date_min', '2026-11-18')
            ->has('horaires', 1)
            ->where('horaires.0.id', $messe->id)
            ->where('horaires.0.label', 'Dimanche — 09:00 — Cathédrale')
            ->where('types.defunt', 'Défunt')
            ->etc());
    }

    public function test_creation_stores_the_intention_and_redirects_with_a_flash_code(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-18 10:00:00', 'Africa/Porto-Novo'));

        $paroisse = Paroisse::factory()->create();
        $messe = Horaire::factory()->create([
            'paroisse_id' => $paroisse->id,
            'type' => 'messe',
        ]);

        $response = $this->post(
            route('paroisse.intentions.store', $paroisse),
            $this->validIntentionPayload($paroisse, [
                'date_souhaitee' => '2026-11-20',
                'horaire_id' => $messe->id,
            ])
        );

        $response->assertRedirect(route('paroisse.intentions.confirmation', $paroisse));
        $response->assertSessionHasNoErrors();

        $code = session('intention_code_suivi');

        $this->assertIsString($code);
        $this->assertTrue(IntentionCode::isValid($code));
        $this->assertStringNotContainsString($code, (string) $response->headers->get('Location'));

        $this->assertDatabaseHas('intentions_messe', [
            'paroisse_id' => $paroisse->id,
            'code_suivi' => $code,
            'nom_demandeur' => 'Jean Dupont',
            'telephone' => '+229 01 23 45 67 89',
            'type' => 'defunt',
            'intention' => 'Pour le repos de l\'âme de Marie.',
            'date_souhaitee' => '2026-11-20 00:00:00',
            'horaire_id' => $messe->id,
            'statut' => 'recue',
        ]);
    }

    public function test_confirmation_shows_the_code_once_and_never_in_the_url(): void
    {
        $paroisse = Paroisse::factory()->create();

        $this->withSession(['intention_code_suivi' => 'ABCDEFGHJK']);

        $response = $this->get(route('paroisse.intentions.confirmation', $paroisse));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Public/Intentions/Confirmation', false)
            ->where('code', 'ABCDEFGHJK')
            ->etc());
        $this->assertSame(
            route('paroisse.intentions.confirmation', $paroisse),
            $this->app['request']->url()
        );

        $this->get(route('paroisse.intentions.confirmation', $paroisse))
            ->assertRedirect(route('paroisse.intentions.nouvelle', $paroisse));
    }

    public function test_suivi_returns_only_status_schedule_and_refusal_reason(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-18 10:00:00', 'Africa/Porto-Novo'));

        $paroisse = Paroisse::factory()->create();
        $horaire = Horaire::factory()->create([
            'paroisse_id' => $paroisse->id,
            'type' => 'messe',
            'heure' => '18:30',
        ]);

        $intention = IntentionMesse::create([
            'paroisse_id' => $paroisse->id,
            'code_suivi' => 'ABCDEFGHJK',
            'nom_demandeur' => 'Nom Confidentiel',
            'telephone' => '+229 01 99 88 77 66',
            'type' => 'defunt',
            'intention' => 'Contenu confidentiel a ne pas divulguer.',
            'date_souhaitee' => '2026-11-20',
            'statut' => 'programmee',
            'date_programmee' => '2026-11-21',
            'horaire_programme_id' => $horaire->id,
            'motif_refus' => null,
        ]);

        $response = $this->post(
            route('paroisse.intentions.suivi.rechercher', $paroisse),
            $this->suiviPayload('abcdefghjk')
        );

        $response->assertOk();

        $props = $this->pageProps($response);

        $this->assertSame([
            'statut' => 'programmee',
            'date_programmee' => '2026-11-21',
            'heure_programmee' => '18:30',
            'motif_refus' => null,
        ], $props['resultat']);
        $this->assertNull($props['message']);

        foreach (['Nom Confidentiel', '+229 01 99 88 77 66', 'Contenu confidentiel a ne pas divulguer.'] as $secret) {
            $this->assertStringNotContainsString($secret, $response->getContent());
        }

        $intention->update([
            'statut' => 'refusee',
            'motif_refus' => 'Horaire indisponible.',
        ]);

        $refused = $this->post(
            route('paroisse.intentions.suivi.rechercher', $paroisse),
            $this->suiviPayload('ABCDEFGHJK')
        );

        $refused->assertOk();

        $this->assertSame([
            'statut' => 'refusee',
            'date_programmee' => '2026-11-21',
            'heure_programmee' => '18:30',
            'motif_refus' => 'Horaire indisponible.',
        ], $this->pageProps($refused)['resultat']);
    }

    public function test_suivi_is_scoped_to_the_current_parish(): void
    {
        $paroisseA = Paroisse::factory()->create();
        $paroisseB = Paroisse::factory()->create();

        IntentionMesse::create([
            'paroisse_id' => $paroisseA->id,
            'code_suivi' => 'ABCDEFGHJK',
            'nom_demandeur' => 'Jean Dupont',
            'type' => 'defunt',
            'intention' => 'Pour Marie.',
            'statut' => 'recue',
        ]);

        $own = $this->post(
            route('paroisse.intentions.suivi.rechercher', $paroisseA),
            $this->suiviPayload('ABCDEFGHJK')
        );

        $own->assertOk();
        $this->assertSame('recue', $this->pageProps($own)['resultat']['statut']);

        $foreign = $this->post(
            route('paroisse.intentions.suivi.rechercher', $paroisseB),
            $this->suiviPayload('ABCDEFGHJK')
        );

        $foreign->assertOk();
        $this->assertNull($this->pageProps($foreign)['resultat']);
    }

    public function test_unknown_and_foreign_codes_return_the_same_neutral_message(): void
    {
        $paroisseA = Paroisse::factory()->create();
        $paroisseB = Paroisse::factory()->create();

        IntentionMesse::create([
            'paroisse_id' => $paroisseA->id,
            'code_suivi' => 'ABCDEFGHJK',
            'nom_demandeur' => 'Jean Dupont',
            'type' => 'defunt',
            'intention' => 'Pour Marie.',
            'statut' => 'recue',
        ]);

        $foreign = $this->post(
            route('paroisse.intentions.suivi.rechercher', $paroisseB),
            $this->suiviPayload('ABCDEFGHJK')
        );
        $unknown = $this->post(
            route('paroisse.intentions.suivi.rechercher', $paroisseB),
            $this->suiviPayload('ZZZZZZZZZZ')
        );

        $foreignProps = $this->pageProps($foreign);
        $unknownProps = $this->pageProps($unknown);
        $neutralMessage = 'Aucune demande ne correspond à ce code pour cette paroisse.';

        $this->assertSame($neutralMessage, $foreignProps['message']);
        $this->assertSame($neutralMessage, $unknownProps['message']);
        $this->assertNull($foreignProps['resultat']);
        $this->assertNull($unknownProps['resultat']);
    }

    public function test_creation_is_rate_limited_to_five_requests_per_hour(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-18 10:00:00', 'Africa/Porto-Novo'));

        $paroisse = Paroisse::factory()->create();

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $response = $this->post(
                route('paroisse.intentions.store', $paroisse),
                $this->validIntentionPayload($paroisse)
            );

            $response->assertRedirect(route('paroisse.intentions.confirmation', $paroisse));
            $response->assertSessionHasNoErrors();
        }

        $this->assertDatabaseCount('intentions_messe', 5);

        $blocked = $this->post(
            route('paroisse.intentions.store', $paroisse),
            $this->validIntentionPayload($paroisse)
        );

        $blocked->assertRedirect();
        $blocked->assertSessionHasErrors([
            'rate_limit' => 'Trop de demandes de messe envoyées. Veuillez réessayer dans une heure.',
        ]);
        $this->assertDatabaseCount('intentions_messe', 5);
    }

    public function test_suivi_is_rate_limited_to_ten_attempts_per_ten_minutes(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-18 10:00:00', 'Africa/Porto-Novo'));

        $paroisse = Paroisse::factory()->create();

        for ($attempt = 1; $attempt <= 10; $attempt++) {
            $response = $this->post(
                route('paroisse.intentions.suivi.rechercher', $paroisse),
                $this->suiviPayload('ZZZZZZZZZZ')
            );

            $response->assertOk();
        }

        $blocked = $this->post(
            route('paroisse.intentions.suivi.rechercher', $paroisse),
            $this->suiviPayload('ZZZZZZZZZZ')
        );

        $blocked->assertRedirect();
        $blocked->assertSessionHasErrors([
            'rate_limit' => 'Trop de tentatives de suivi. Veuillez réessayer dans quelques minutes.',
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, array<int, string>>
     */
    private function storeErrors(array $payload, Paroisse $paroisse): array
    {
        $request = StoreIntentionRequest::create(
            '/p/'.$paroisse->slug.'/intentions/nouvelle',
            'POST',
            $payload
        );
        $request->setContainer($this->app);

        $route = new Route(['POST'], '/p/{paroisse}/intentions/nouvelle', []);
        $route->bind($request);
        $route->setParameter('paroisse', $paroisse);
        $request->setRouteResolver(static fn (): Route => $route);

        return Validator::make(
            $request->all(),
            $request->rules(),
            $request->messages(),
            $request->attributes()
        )->errors()->toArray();
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, array<int, string>>
     */
    private function suiviErrors(array $payload): array
    {
        $request = SuivreIntentionRequest::create('/p/test/intentions/suivi', 'POST', $payload);
        $request->setContainer($this->app);

        return Validator::make(
            $request->all(),
            $request->rules(),
            $request->messages(),
            $request->attributes()
        )->errors()->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    private function suiviPayload(string $code, array $overrides = []): array
    {
        return array_merge([
            'code_suivi' => $code,
            'reference_colis' => '',
            'form_token' => $this->validToken(),
        ], $overrides);
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

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validIntentionPayload(Paroisse $paroisse, array $overrides = []): array
    {
        return array_merge([
            'nom_demandeur' => 'Jean Dupont',
            'telephone' => '+229 01 23 45 67 89',
            'type' => 'defunt',
            'intention' => 'Pour le repos de l\'âme de Marie.',
            'date_souhaitee' => Carbon::today('Africa/Porto-Novo')->toDateString(),
            'horaire_id' => null,
            'reference_colis' => '',
            'form_token' => $this->validToken(),
        ], $overrides);
    }

    private function validToken(int $ageSeconds = 4): string
    {
        $issuedAt = Carbon::now();
        $token = PublicFormToken::issue($issuedAt);
        Carbon::setTestNow($issuedAt->copy()->addSeconds($ageSeconds));

        return $token;
    }

    private function expiredToken(): string
    {
        $issuedAt = Carbon::now();
        $token = PublicFormToken::issue($issuedAt);
        Carbon::setTestNow($issuedAt->copy()->addSeconds(PublicFormToken::MAX_AGE_SECONDS + 1));

        return $token;
    }

    private function tamperedToken(): string
    {
        [$timestamp, $signature] = explode('.', $this->validToken());
        $signature[0] = $signature[0] === 'a' ? 'b' : 'a';

        return $timestamp.'.'.$signature;
    }
}
