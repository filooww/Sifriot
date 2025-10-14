<?php

namespace Tests\Feature\E2E;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationFlowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function complete_user_registration_flow()
    {
        // Visit registration page
        $response = $this->get('/register');
        $response->assertStatus(200);

        // Submit registration form
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Should redirect to dashboard after successful registration
        $response->assertRedirect('/dashboard');

        // Verify user was created in database
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // User should be authenticated
        $this->assertAuthenticated();
    }

    /** @test */
    public function complete_user_login_flow()
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'testuser@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Visit login page
        $response = $this->get('/login');
        $response->assertStatus(200);

        // Submit login form
        $response = $this->post('/login', [
            'email' => 'testuser@example.com',
            'password' => 'password123',
        ]);

        // Should redirect to dashboard after successful login
        $response->assertRedirect('/dashboard');

        // User should be authenticated
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function user_cannot_login_with_incorrect_password()
    {
        $user = User::factory()->create([
            'email' => 'testuser@example.com',
            'password' => Hash::make('correct-password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'testuser@example.com',
            'password' => 'wrong-password',
        ]);

        // Should stay on login page with errors
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** @test */
    public function complete_user_logout_flow()
    {
        $user = User::factory()->create();

        // Login user
        $this->actingAs($user);
        $this->assertAuthenticated();

        // Visit dashboard to verify authenticated
        $response = $this->get('/dashboard');
        $response->assertStatus(200);

        // Logout
        $response = $this->post('/logout');

        // Should be redirected to home page
        $response->assertRedirect('/');

        // User should no longer be authenticated
        $this->assertGuest();
    }

    /** @test */
    public function complete_password_reset_flow()
    {
        $user = User::factory()->create([
            'email' => 'testuser@example.com',
        ]);

        // Visit forgot password page
        $response = $this->get('/forgot-password');
        $response->assertStatus(200);

        // Request password reset
        $response = $this->post('/forgot-password', [
            'email' => 'testuser@example.com',
        ]);

        $response->assertSessionHas('status');

        // Note: In a real E2E test with Dusk, you would:
        // 1. Get the reset token from email
        // 2. Visit the reset password page with token
        // 3. Submit new password
        // 4. Verify login with new password
    }

    /** @test */
    public function guest_is_redirected_to_login_when_accessing_protected_routes()
    {
        // Try to access dashboard without authentication
        $response = $this->get('/dashboard');

        // Should redirect to login
        $response->assertRedirect('/login');
    }

    /** @test */
    public function authenticated_user_can_access_dashboard()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
    }

    /** @test */
    public function authenticated_user_can_access_profile()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $response = $this->actingAs($user)->get('/profile');

        $response->assertStatus(200);
        $response->assertSee('john@example.com');
    }

    /** @test */
    public function user_registration_validates_email_uniqueness()
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function user_registration_validates_password_confirmation()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    /** @test */
    public function complete_authentication_journey()
    {
        // 1. Guest visits publications page (public)
        $response = $this->get('/publications');
        $response->assertStatus(200);

        // 2. Guest tries to access dashboard (protected)
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');

        // 3. Guest registers new account
        $response = $this->post('/register', [
            'name' => 'Journey User',
            'email' => 'journey@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        // 4. User can now access dashboard
        $response = $this->get('/dashboard');
        $response->assertStatus(200);

        // 5. User visits profile
        $response = $this->get('/profile');
        $response->assertStatus(200);

        // 6. User logs out
        $response = $this->post('/logout');
        $response->assertRedirect('/');
        $this->assertGuest();

        // 7. User can log back in
        $response = $this->post('/login', [
            'email' => 'journey@example.com',
            'password' => 'password123',
        ]);
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }
}
