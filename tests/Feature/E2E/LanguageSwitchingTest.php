<?php

namespace Tests\Feature\E2E;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LanguageSwitchingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_switch_to_russian_language()
    {
        $response = $this->get('/language/ru');

        $response->assertRedirect();
        $this->assertEquals('ru', session('locale'));
    }

    /** @test */
    public function user_can_switch_to_english_language()
    {
        $response = $this->get('/language/en');

        $response->assertRedirect();
        $this->assertEquals('en', session('locale'));
    }

    /** @test */
    public function invalid_locale_is_ignored()
    {
        $response = $this->get('/language/invalid');

        $response->assertRedirect();
        $this->assertNull(session('locale'));
    }

    /** @test */
    public function language_switching_redirects_back_to_previous_page()
    {
        // Visit publications page
        $this->get('/publications');

        // Switch language
        $response = $this->get('/language/ru');

        // Should redirect back
        $response->assertRedirect();
    }

    /** @test */
    public function language_preference_persists_across_requests()
    {
        // Set language to Russian
        $this->get('/language/ru');
        $this->assertEquals('ru', session('locale'));

        // Make another request
        $this->get('/publications');

        // Language should still be Russian
        $this->assertEquals('ru', session('locale'));
    }

    /** @test */
    public function authenticated_user_can_switch_language()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/language/ru');

        $response->assertRedirect();
        $this->assertEquals('ru', session('locale'));
    }

    /** @test */
    public function guest_user_can_switch_language()
    {
        $response = $this->get('/language/en');

        $response->assertRedirect();
        $this->assertEquals('en', session('locale'));
    }

    /** @test */
    public function language_switching_works_with_query_parameters()
    {
        // Visit page with query parameters
        $this->get('/publications?search=test');

        // Switch language
        $response = $this->get('/language/ru');

        $response->assertRedirect();
        $this->assertEquals('ru', session('locale'));
    }

    /** @test */
    public function complete_multilingual_user_journey()
    {
        // 1. Guest visits site (default language)
        $response = $this->get('/publications');
        $response->assertStatus(200);

        // 2. Guest switches to Russian
        $this->get('/language/ru');
        $this->assertEquals('ru', session('locale'));

        // 3. Guest registers (language preference should persist)
        $this->post('/register', [
            'name' => 'Multilingual User',
            'email' => 'multi@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertEquals('ru', session('locale'));

        // 4. User switches to English while authenticated
        $this->get('/language/en');
        $this->assertEquals('en', session('locale'));

        // 5. User logs out (language preference in session)
        $this->post('/logout');

        // 6. User logs back in
        $this->post('/login', [
            'email' => 'multi@example.com',
            'password' => 'password123',
        ]);

        // Note: Language preference is session-based, so it may reset
        // Depending on implementation, you might want to persist it in user profile
    }

    /** @test */
    public function only_supported_locales_are_accepted()
    {
        $supportedLocales = ['en', 'ru'];

        foreach ($supportedLocales as $locale) {
            $response = $this->get("/language/{$locale}");
            $response->assertRedirect();
            $this->assertEquals($locale, session('locale'));
        }

        // Test unsupported locale
        $this->get('/language/fr');
        $this->assertNotEquals('fr', session('locale'));
    }

    /** @test */
    public function language_route_is_named_correctly()
    {
        $url = route('language.switch', ['locale' => 'ru']);

        $this->assertEquals('http://localhost/language/ru', $url);
    }
}
