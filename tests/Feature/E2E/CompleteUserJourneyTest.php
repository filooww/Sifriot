<?php

namespace Tests\Feature\E2E;

use App\Models\Publication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * This test simulates a complete user journey through the application,
 * testing the integration of all major features.
 */
class CompleteUserJourneyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function complete_application_user_journey()
    {
        // ========================================
        // PHASE 1: Guest User Experience
        // ========================================

        // 1. Guest lands on homepage
        $response = $this->get('/');
        $response->assertRedirect(route('publications.index'));

        // 2. Guest views publications list
        $publication1 = Publication::factory()->create([
            'title' => 'Laravel Best Practices 2024',
            'title_low' => 'laravel best practices 2024',
            '_del_mark' => 0,
        ]);

        $publication2 = Publication::factory()->create([
            'title' => 'Advanced Testing Strategies',
            'title_low' => 'advanced testing strategies',
            '_del_mark' => 0,
        ]);

        $response = $this->get('/publications');
        $response->assertStatus(200);
        $response->assertSee('Laravel Best Practices 2024');
        $response->assertSee('Advanced Testing Strategies');

        // 3. Guest searches for publications
        Livewire::test('publications.publication-list')
            ->set('search', 'Laravel')
            ->assertSee('Laravel Best Practices 2024')
            ->assertDontSee('Advanced Testing Strategies');

        // 4. Guest tries to access protected route
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');

        // 5. Guest switches language to Russian
        $response = $this->get('/language/ru');
        $this->assertEquals('ru', session('locale'));

        // ========================================
        // PHASE 2: User Registration
        // ========================================

        // 6. Guest decides to register
        $response = $this->get('/register');
        $response->assertStatus(200);

        // 7. Guest submits registration
        $response = $this->post('/register', [
            'name' => 'Alex Johnson',
            'email' => 'alex@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $user = User::where('email', 'alex@example.com')->first();
        $this->assertNotNull($user);

        // ========================================
        // PHASE 3: Authenticated User Actions
        // ========================================

        // 8. User views dashboard
        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Dashboard');

        // 9. User switches language to English
        $response = $this->actingAs($user)->get('/language/en');
        $this->assertEquals('en', session('locale'));

        // 10. User views publications (still accessible)
        $response = $this->actingAs($user)->get('/publications');
        $response->assertStatus(200);

        // 11. User can still search publications
        Livewire::actingAs($user)
            ->test('publications.publication-list')
            ->set('search', 'Testing')
            ->assertSee('Advanced Testing Strategies')
            ->assertDontSee('Laravel Best Practices 2024');

        // ========================================
        // PHASE 4: Profile Management
        // ========================================

        // 12. User visits profile page
        $response = $this->actingAs($user)->get('/profile');
        $response->assertStatus(200);
        $response->assertSee('Alex Johnson');
        $response->assertSee('alex@example.com');

        // 13. User updates profile information
        Livewire::actingAs($user)
            ->test('profile.update-profile-information-form')
            ->set('name', 'Alexander Johnson')
            ->set('email', 'alex@example.com')
            ->call('updateProfileInformation');

        $user->refresh();
        $this->assertEquals('Alexander Johnson', $user->name);

        // 14. User changes password
        Livewire::actingAs($user)
            ->test('profile.update-password-form')
            ->set('current_password', 'SecurePassword123!')
            ->set('password', 'NewSecurePassword456!')
            ->set('password_confirmation', 'NewSecurePassword456!')
            ->call('updatePassword');

        $user->refresh();
        $this->assertTrue(Hash::check('NewSecurePassword456!', $user->password));

        // ========================================
        // PHASE 5: Session Management
        // ========================================

        // 15. User logs out
        $response = $this->actingAs($user)->post('/logout');
        $response->assertRedirect('/');
        $this->assertGuest();

        // 16. User can still view public pages
        $response = $this->get('/publications');
        $response->assertStatus(200);

        // 17. User cannot access protected pages
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');

        // 18. User logs back in with new password
        $response = $this->post('/login', [
            'email' => 'alex@example.com',
            'password' => 'NewSecurePassword456!',
        ]);
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        // 19. User accesses dashboard again
        $response = $this->get('/dashboard');
        $response->assertStatus(200);

        // ========================================
        // PHASE 6: Advanced Publication Features
        // ========================================

        // 20. Create a publication that will be deleted
        $deletablePublication = Publication::factory()->create([
            'title' => 'Temporary Publication',
            'title_low' => 'temporary publication',
            '_del_mark' => 0,
        ]);

        // 21. Soft delete a publication
        Livewire::actingAs($user)
            ->test('publications.publication-list')
            ->call('deletePublication', $deletablePublication->id_publication);

        $this->assertDatabaseHas('publications', [
            'id_publication' => $deletablePublication->id_publication,
            '_del_mark' => 1,
        ]);

        // 22. Toggle to view deleted publications
        Livewire::actingAs($user)
            ->test('publications.publication-list')
            ->set('showDeleted', true)
            ->assertSee('Temporary Publication');

        // 23. Restore the deleted publication
        Livewire::actingAs($user)
            ->test('publications.publication-list')
            ->call('restorePublication', $deletablePublication->id_publication);

        $this->assertDatabaseHas('publications', [
            'id_publication' => $deletablePublication->id_publication,
            '_del_mark' => 0,
        ]);

        // ========================================
        // PHASE 7: Final Verification
        // ========================================

        // 24. Verify all created data still exists
        $this->assertDatabaseHas('users', [
            'email' => 'alex@example.com',
            'name' => 'Alexander Johnson',
        ]);

        $this->assertDatabaseHas('publications', [
            'title' => 'Laravel Best Practices 2024',
            '_del_mark' => 0,
        ]);

        $this->assertDatabaseHas('publications', [
            'title' => 'Advanced Testing Strategies',
            '_del_mark' => 0,
        ]);

        // 25. Final logout
        $response = $this->post('/logout');
        $response->assertRedirect('/');
        $this->assertGuest();
    }

    /** @test */
    public function multiple_users_can_interact_independently()
    {
        // Create publications
        $publication = Publication::factory()->create([
            'title' => 'Shared Publication',
            '_del_mark' => 0,
        ]);

        // User 1 registers and interacts
        $this->post('/register', [
            'name' => 'User One',
            'email' => 'user1@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user1 = User::where('email', 'user1@example.com')->first();

        // User 1 searches
        Livewire::actingAs($user1)
            ->test('publications.publication-list')
            ->set('search', 'Shared')
            ->assertSee('Shared Publication');

        // Logout User 1
        $this->post('/logout');

        // User 2 registers and interacts
        $this->post('/register', [
            'name' => 'User Two',
            'email' => 'user2@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user2 = User::where('email', 'user2@example.com')->first();

        // User 2 can also see and search publications
        Livewire::actingAs($user2)
            ->test('publications.publication-list')
            ->set('search', 'Shared')
            ->assertSee('Shared Publication');

        // Both users exist independently
        $this->assertDatabaseHas('users', ['email' => 'user1@example.com']);
        $this->assertDatabaseHas('users', ['email' => 'user2@example.com']);
    }
}
