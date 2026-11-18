<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Tests\TestCase;

class RequiredPasswordChangeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_disabled_user_cannot_authenticate(): void
    {
        $user = User::factory()->create(['actif' => false]);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_successful_login_updates_last_login_timestamp(): void
    {
        $user = User::factory()->create();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard', absolute: false));

        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_disabling_a_logged_in_user_invalidates_the_next_inertia_request(): void
    {
        $user = User::factory()->create();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard', absolute: false));
        $this->assertAuthenticatedAs($user);

        $sessionId = $this->app['session.store']->getId();
        DB::table('users')->where('id', $user->id)->update(['actif' => false]);
        Auth::forgetGuards();

        $response = $this->withHeaders([
            'X-Inertia' => 'true',
            'X-Inertia-Version' => Inertia::getVersion() ?? '',
        ])->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertNotSame($sessionId, $this->app['session.store']->getId());
    }

    public function test_user_must_change_temporary_password_before_accessing_other_pages(): void
    {
        $user = User::factory()->create([
            'must_change_password' => true,
        ]);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard', absolute: false));

        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('password.force'));

        $changePage = $this->get(route('password.force'));
        $changePage->assertOk();
        $this->assertSame(
            'Auth/ChangePassword',
            $changePage->viewData('page')['component']
        );

        $newPassword = 'NouveauMotDePasse2026';
        $this->put(route('password.force.update'), [
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ])->assertRedirect(route('admin.dashboard', absolute: false));

        $this->assertFalse($user->fresh()->must_change_password);
        $this->assertTrue(Hash::check($newPassword, $user->fresh()->password));
        $this->get(route('admin.dashboard'))->assertOk();
    }

    public function test_user_without_required_password_change_cannot_use_the_forced_change_endpoint(): void
    {
        $user = User::factory()->create(['must_change_password' => false]);

        $this->actingAs($user)
            ->get(route('password.force'))
            ->assertForbidden();

        $this->actingAs($user)
            ->put(route('password.force.update'), [
                'password' => 'NouveauMotDePasse2026',
                'password_confirmation' => 'NouveauMotDePasse2026',
            ])
            ->assertForbidden();
    }
}
