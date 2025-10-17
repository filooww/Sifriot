<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class EnsureUserRoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Define test routes for middleware testing
        Route::get('/test-user-route', function () {
            return 'user route';
        })->middleware(['role:user'])->name('test.user');

        Route::get('/test-admin-route', function () {
            return 'admin route';
        })->middleware(['role:admin'])->name('test.admin');
    }

    public function test_guest_redirected_to_login_for_user_route(): void
    {
        $response = $this->get('/test-user-route');

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_user_role_route(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/test-user-route');

        $response->assertStatus(200);
        $response->assertSee('user route');
    }

    public function test_non_admin_user_blocked_from_admin_route(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/test-admin-route');

        $response->assertStatus(403);
    }

    public function test_admin_user_can_access_admin_route(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/test-admin-route');

        $response->assertStatus(200);
        $response->assertSee('admin route');
    }

    public function test_guest_redirected_from_admin_route(): void
    {
        $response = $this->get('/test-admin-route');

        $response->assertRedirect(route('login'));
    }

    public function test_intended_url_stored_when_guest_redirected(): void
    {
        $response = $this->get('/test-user-route');

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('intended');
    }
}
