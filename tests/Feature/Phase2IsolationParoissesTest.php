<?php

namespace Tests\Feature;

use App\Models\IntentionMesse;
use App\Models\MessagePrive;
use App\Models\Paroisse;
use App\Models\User;
use App\Support\Permission;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class Phase2IsolationParoissesTest extends TestCase
{
    use RefreshDatabase;

    public function test_global_scope_limits_intentions_and_messages_to_the_authenticated_paroisse(): void
    {
        $paroisseA = Paroisse::factory()->create();
        $paroisseB = Paroisse::factory()->create();
        $intentionA = $this->createIntention($paroisseA->id, 'CODEA00001');
        $intentionB = $this->createIntention($paroisseB->id, 'CODEB00001');
        $messageA = $this->createMessage($paroisseA->id);
        $messageB = $this->createMessage($paroisseB->id);

        $this->actingAs(User::factory()->admin()->create(['paroisse_id' => $paroisseA->id]));

        $this->assertSame([$intentionA->id], IntentionMesse::all()->pluck('id')->all());
        $this->assertSame([$messageA->id], MessagePrive::all()->pluck('id')->all());
        $this->assertNull(IntentionMesse::find($intentionB->id));
        $this->assertNull(MessagePrive::find($messageB->id));
        $this->assertNotNull(IntentionMesse::forParoisse($paroisseB->id)->find($intentionB->id));
        $this->assertNotNull(MessagePrive::forParoisse($paroisseB->id)->find($messageB->id));
    }

    public function test_find_or_fail_hides_records_from_another_paroisse(): void
    {
        $paroisseA = Paroisse::factory()->create();
        $paroisseB = Paroisse::factory()->create();
        $intentionB = $this->createIntention($paroisseB->id, 'CODEB00002');
        $messageB = $this->createMessage($paroisseB->id);

        $this->actingAs(User::factory()->admin()->create(['paroisse_id' => $paroisseA->id]));

        $this->assertThrows(
            fn () => IntentionMesse::findOrFail($intentionB->id),
            ModelNotFoundException::class
        );
        $this->assertThrows(
            fn () => MessagePrive::findOrFail($messageB->id),
            ModelNotFoundException::class
        );
    }

    public function test_policies_deny_management_of_records_from_another_paroisse(): void
    {
        $paroisseA = Paroisse::factory()->create();
        $paroisseB = Paroisse::factory()->create();
        $intentionA = $this->createIntention($paroisseA->id, 'CODEA00002');
        $intentionB = $this->createIntention($paroisseB->id, 'CODEB00003');
        $messageA = $this->createMessage($paroisseA->id);
        $messageB = $this->createMessage($paroisseB->id);
        $adminA = User::factory()->admin()->create(['paroisse_id' => $paroisseA->id]);

        $this->assertTrue($adminA->can('view', $intentionA));
        $this->assertFalse($adminA->can('view', $intentionB));
        $this->assertFalse($adminA->can('update', $intentionB));
        $this->assertFalse($adminA->can('delete', $intentionB));

        $this->assertTrue($adminA->can('view', $messageA));
        $this->assertFalse($adminA->can('view', $messageB));
        $this->assertFalse($adminA->can('update', $messageB));
        $this->assertFalse($adminA->can('delete', $messageB));
    }

    public function test_secretariat_without_intentions_gerer_is_denied_and_with_it_is_allowed(): void
    {
        $paroisse = Paroisse::factory()->create();
        $intention = $this->createIntention($paroisse->id, 'CODEA00003');
        $message = $this->createMessage($paroisse->id);
        $secretariat = User::factory()->create([
            'paroisse_id' => $paroisse->id,
            'permissions' => [],
        ]);

        $this->assertFalse($secretariat->can('viewAny', IntentionMesse::class));
        $this->assertFalse($secretariat->can('create', IntentionMesse::class));
        $this->assertFalse($secretariat->can('view', $intention));
        $this->assertFalse($secretariat->can('update', $intention));
        $this->assertFalse($secretariat->can('delete', $intention));

        $secretariat->update(['permissions' => [Permission::IntentionsGerer->value]]);

        $this->assertTrue($secretariat->can('viewAny', IntentionMesse::class));
        $this->assertTrue($secretariat->can('create', IntentionMesse::class));
        $this->assertTrue($secretariat->can('view', $intention));
        $this->assertTrue($secretariat->can('update', $intention));
        $this->assertTrue($secretariat->can('delete', $intention));
        $this->assertFalse($secretariat->can('view', $message));
    }

    public function test_secretariat_without_messages_lire_is_denied_and_cannot_delete_even_with_it(): void
    {
        $paroisse = Paroisse::factory()->create();
        $message = $this->createMessage($paroisse->id);
        $secretariat = User::factory()->create(['paroisse_id' => $paroisse->id]);

        $this->assertFalse($secretariat->can('viewAny', MessagePrive::class));
        $this->assertFalse($secretariat->can('create', MessagePrive::class));
        $this->assertFalse($secretariat->can('view', $message));
        $this->assertFalse($secretariat->can('update', $message));
        $this->assertFalse($secretariat->can('delete', $message));

        $secretariat->update(['permissions' => [Permission::MessagesLire->value]]);

        $this->assertTrue($secretariat->can('viewAny', MessagePrive::class));
        $this->assertTrue($secretariat->can('view', $message));
        $this->assertTrue($secretariat->can('update', $message));
        $this->assertFalse($secretariat->can('delete', $message));

        $admin = User::factory()->admin()->create(['paroisse_id' => $paroisse->id]);
        $this->assertTrue($admin->can('delete', $message));
    }

    public function test_default_permissions_grant_intentions_gerer_but_never_messages_lire(): void
    {
        $paroisse = Paroisse::factory()->create();
        $secretariat = User::factory()->create(['paroisse_id' => $paroisse->id]);
        $admin = User::factory()->admin()->create(['paroisse_id' => $paroisse->id]);

        $this->assertSame([
            Permission::AnnoncesGerer->value,
            Permission::HorairesGerer->value,
            Permission::IntentionsGerer->value,
        ], $secretariat->permissions);
        $this->assertTrue($secretariat->hasPermission(Permission::IntentionsGerer));
        $this->assertFalse($secretariat->hasPermission(Permission::MessagesLire));
        $this->assertTrue($admin->hasPermission(Permission::IntentionsGerer));
        $this->assertTrue($admin->hasPermission(Permission::MessagesLire));
    }

    public function test_backfill_migration_adds_intentions_gerer_without_adding_messages_lire(): void
    {
        $paroisse = Paroisse::factory()->create();
        $secretariat = User::factory()->create([
            'paroisse_id' => $paroisse->id,
            'permissions' => [Permission::AnnoncesGerer->value],
        ]);
        $migration = require database_path('migrations/2026_11_18_105857_add_intentions_gerer_to_existing_secretariats.php');

        $migration->down();
        $migration->up();
        $migration->up();

        $this->assertSame([
            Permission::AnnoncesGerer->value,
            Permission::IntentionsGerer->value,
        ], $secretariat->fresh()->permissions);
        $this->assertFalse($secretariat->fresh()->hasPermission(Permission::MessagesLire));
    }

    public function test_creating_a_record_for_another_paroisse_is_rejected(): void
    {
        $paroisseA = Paroisse::factory()->create();
        $paroisseB = Paroisse::factory()->create();

        $this->actingAs(User::factory()->admin()->create(['paroisse_id' => $paroisseA->id]));

        $this->assertThrows(
            fn () => $this->createIntention($paroisseB->id, 'CODEB00004'),
            RuntimeException::class
        );
        $this->assertThrows(
            fn () => $this->createMessage($paroisseB->id),
            RuntimeException::class
        );
    }

    public function test_code_suivi_is_unique(): void
    {
        $paroisse = Paroisse::factory()->create();
        $this->createIntention($paroisse->id, 'CODEA00004');

        $this->assertThrows(
            fn () => $this->createIntention($paroisse->id, 'CODEA00004'),
            QueryException::class
        );
    }

    private function createIntention(int $paroisseId, string $codeSuivi): IntentionMesse
    {
        return IntentionMesse::create([
            'paroisse_id' => $paroisseId,
            'code_suivi' => $codeSuivi,
            'nom_demandeur' => 'Jean Dupont',
            'telephone' => '+229 01 23 45 67 89',
            'type' => 'defunt',
            'intention' => 'Pour le repos de l’âme de Marie.',
            'date_souhaitee' => '2026-11-25',
            'statut' => 'recue',
        ]);
    }

    private function createMessage(int $paroisseId): MessagePrive
    {
        return MessagePrive::create([
            'paroisse_id' => $paroisseId,
            'sujet' => 'suggestion',
            'message' => 'Une suggestion pour la paroisse.',
            'nom' => 'Aimé Agbodjan',
            'contact' => '+229 01 23 45 67 89',
            'souhaite_reponse' => false,
            'statut' => 'nouveau',
        ]);
    }
}
