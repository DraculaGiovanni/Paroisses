<?php

namespace Tests\Feature;

use App\Models\Paroisse;
use App\Models\User;
use App\Support\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShareInertiaDataTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function guest_receives_no_sensitive_data()
    {
        $response = $this->get('/');

        $response->assertSuccessful();

        // The auth object is shared, but its user value is explicitly null for guests.
        $page = $response->viewData('page');
        $this->assertArrayHasKey('auth', $page['props']);
        $this->assertNull($page['props']['auth']['user']);
        $this->assertNull($page['props']['paroisse']);
    }

    #[Test]
    public function authenticated_admin_receives_only_their_paroisse()
    {
        $paroisse1 = Paroisse::factory()->create(['nom' => 'Paroisse 1']);
        $paroisse2 = Paroisse::factory()->create(['nom' => 'Paroisse 2']);

        $admin = User::factory()->admin()->create(['paroisse_id' => $paroisse1->id]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $page = $response->viewData('page');

        // A accès aux props auth et paroisse
        $this->assertArrayHasKey('auth', $page['props']);
        $this->assertArrayHasKey('paroisse', $page['props']);

        // Ne voit que sa paroisse
        $this->assertEquals('Paroisse 1', $page['props']['paroisse']['nom']);
        $this->assertNotEquals('Paroisse 2', $page['props']['paroisse']['nom']);
    }

    #[Test]
    public function authenticated_secretariat_receives_only_its_permissions()
    {
        $paroisse = Paroisse::factory()->create();
        $secretariat = User::factory()->create([
            'paroisse_id' => $paroisse->id,
            'permissions' => [Permission::HorairesGerer->value],
        ]);

        $response = $this->actingAs($secretariat)->get(route('admin.dashboard'));
        $permissions = $response->viewData('page')['props']['auth']['user']['permissions'];

        $this->assertSame([Permission::HorairesGerer->value], $permissions);
    }

    #[Test]
    public function admin_without_paroisse_receives_null_paroisse()
    {
        $orphan = User::factory()->admin()->create(['paroisse_id' => null]);

        // Un admin sans paroisse est refusé sur /admin/* (middleware paroisse.isolation) :
        // on vérifie le partage Inertia sur une page accessible, sans ce middleware.
        $response = $this->actingAs($orphan)->get(route('profile.edit'));

        $page = $response->viewData('page');

        $this->assertArrayHasKey('auth', $page['props']);
        $this->assertNull($page['props']['paroisse']);
    }

    #[Test]
    public function flash_messages_are_shared()
    {
        $paroisse = Paroisse::factory()->create();
        $admin = User::factory()->admin()->create(['paroisse_id' => $paroisse->id]);

        $response = $this->actingAs($admin)
            ->followingRedirects()
            ->put(route('admin.paroisse.update'), [
                'nom' => $paroisse->nom,
            ]);

        $page = $response->viewData('page');

        // Message flash présent
        $this->assertArrayHasKey('flash', $page['props']);
        $this->assertNotNull($page['props']['flash']);
    }
}
