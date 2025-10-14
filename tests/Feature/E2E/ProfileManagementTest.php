<?php

namespace Tests\Feature\E2E;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileManagementTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function authenticated_user_can_view_profile_page()
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $response = $this->actingAs($user)->get('/profile');

        $response->assertStatus(200);
        $response->assertSee('Test User');
        $response->assertSee('test@example.com');
    }

    /** @test */
    public function guest_cannot_access_profile_page()
    {
        $response = $this->get('/profile');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function user_can_update_profile_information()
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
        ]);

        $this->actingAs($user);

        Livewire::test('profile.update-profile-information-form')
            ->set('name', 'New Name')
            ->set('email', 'new@example.com')
            ->call('updateProfileInformation');

        $user->refresh();

        $this->assertEquals('New Name', $user->name);
        $this->assertEquals('new@example.com', $user->email);
    }

    /** @test */
    public function profile_information_update_requires_valid_email()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test('profile.update-profile-information-form')
            ->set('name', 'Test Name')
            ->set('email', 'invalid-email')
            ->call('updateProfileInformation')
            ->assertHasErrors(['email' => 'email']);
    }

    /** @test */
    public function email_verification_required_when_email_is_changed()
    {
        $user = User::factory()->create([
            'email' => 'original@example.com',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        Livewire::test('profile.update-profile-information-form')
            ->set('email', 'changed@example.com')
            ->call('updateProfileInformation');

        $user->refresh();

        // Email should be changed but verification should be null
        $this->assertEquals('changed@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    /** @test */
    public function user_can_update_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $this->actingAs($user);

        Livewire::test('profile.update-password-form')
            ->set('current_password', 'old-password')
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('updatePassword');

        $user->refresh();

        $this->assertTrue(Hash::check('new-password', $user->password));
    }

    /** @test */
    public function password_update_requires_current_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('current-password'),
        ]);

        $this->actingAs($user);

        Livewire::test('profile.update-password-form')
            ->set('current_password', 'wrong-password')
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('updatePassword')
            ->assertHasErrors(['current_password']);
    }

    /** @test */
    public function password_update_requires_confirmation()
    {
        $user = User::factory()->create([
            'password' => Hash::make('current-password'),
        ]);

        $this->actingAs($user);

        Livewire::test('profile.update-password-form')
            ->set('current_password', 'current-password')
            ->set('password', 'new-password')
            ->set('password_confirmation', 'different-password')
            ->call('updatePassword')
            ->assertHasErrors(['password']);
    }

    /** @test */
    public function user_can_delete_their_account()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $this->actingAs($user);

        Livewire::test('profile.delete-user-form')
            ->set('password', 'password123')
            ->call('deleteUser');

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);

        // User should be logged out
        $this->assertGuest();
    }

    /** @test */
    public function account_deletion_requires_correct_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $this->actingAs($user);

        Livewire::test('profile.delete-user-form')
            ->set('password', 'wrong-password')
            ->call('deleteUser')
            ->assertHasErrors(['password']);

        // User should still exist
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
        ]);
    }

    /** @test */
    public function complete_profile_management_journey()
    {
        // 1. User registers
        $this->post('/register', [
            'name' => 'Journey User',
            'email' => 'journey@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'journey@example.com')->first();
        $this->assertNotNull($user);

        // 2. User visits profile
        $response = $this->actingAs($user)->get('/profile');
        $response->assertStatus(200);
        $response->assertSee('Journey User');

        // 3. User updates their name
        Livewire::actingAs($user)
            ->test('profile.update-profile-information-form')
            ->set('name', 'Updated Journey User')
            ->set('email', 'journey@example.com')
            ->call('updateProfileInformation');

        $user->refresh();
        $this->assertEquals('Updated Journey User', $user->name);

        // 4. User changes password
        Livewire::actingAs($user)
            ->test('profile.update-password-form')
            ->set('current_password', 'password123')
            ->set('password', 'new-secure-password')
            ->set('password_confirmation', 'new-secure-password')
            ->call('updatePassword');

        // 5. Verify user can login with new password
        $this->post('/logout');

        $response = $this->post('/login', [
            'email' => 'journey@example.com',
            'password' => 'new-secure-password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    /** @test */
    public function profile_form_validation_works_correctly()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        // Test empty name
        Livewire::test('profile.update-profile-information-form')
            ->set('name', '')
            ->set('email', 'test@example.com')
            ->call('updateProfileInformation')
            ->assertHasErrors(['name' => 'required']);

        // Test empty email
        Livewire::test('profile.update-profile-information-form')
            ->set('name', 'Test Name')
            ->set('email', '')
            ->call('updateProfileInformation')
            ->assertHasErrors(['email' => 'required']);
    }

    /** @test */
    public function password_must_meet_minimum_length_requirement()
    {
        $user = User::factory()->create([
            'password' => Hash::make('current-password'),
        ]);

        $this->actingAs($user);

        Livewire::test('profile.update-password-form')
            ->set('current_password', 'current-password')
            ->set('password', 'short')
            ->set('password_confirmation', 'short')
            ->call('updatePassword')
            ->assertHasErrors(['password' => 'min']);
    }
}
