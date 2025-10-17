<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LanguageSwitchingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_switch_language_to_english(): void
    {
        $response = $this->get('/language/en');

        $response->assertRedirect();
        $this->assertEquals('en', session('locale'));
    }

    public function test_guest_can_switch_language_to_russian(): void
    {
        $response = $this->get('/language/ru');

        $response->assertRedirect();
        $this->assertEquals('ru', session('locale'));
    }

    public function test_guest_can_switch_language_to_hebrew(): void
    {
        $response = $this->get('/language/he');

        $response->assertRedirect();
        $this->assertEquals('he', session('locale'));
    }

    public function test_invalid_locale_is_rejected(): void
    {
        $response = $this->get('/language/invalid');

        $response->assertRedirect();
        $this->assertNull(session('locale'));
    }

    public function test_authenticated_user_language_preference_is_saved_to_database(): void
    {
        $user = User::factory()->create(['preferred_language' => 'en']);

        $this->actingAs($user)
            ->get('/language/ru');

        $this->assertEquals('ru', $user->fresh()->preferred_language);
        $this->assertEquals('ru', session('locale'));
    }

    public function test_authenticated_user_language_preference_persists_across_requests(): void
    {
        $user = User::factory()->create(['preferred_language' => 'he']);

        $this->actingAs($user)
            ->get('/dashboard');

        $this->assertEquals('he', app()->getLocale());
    }

    public function test_middleware_loads_user_preference_over_session(): void
    {
        $user = User::factory()->create(['preferred_language' => 'ru']);

        // Set session to different language
        session(['locale' => 'en']);

        $this->actingAs($user)
            ->get('/dashboard');

        // User preference should override session
        $this->assertEquals('ru', app()->getLocale());
        $this->assertEquals('ru', session('locale'));
    }

    public function test_hebrew_language_activates_rtl_mode(): void
    {
        $user = User::factory()->create(['preferred_language' => 'he']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertSee('dir="rtl"', false);
        $response->assertSee('lang="he"', false);
    }

    public function test_english_and_russian_use_ltr_mode(): void
    {
        $userEn = User::factory()->create(['preferred_language' => 'en']);
        $response = $this->actingAs($userEn)->get('/dashboard');
        $response->assertSee('dir="ltr"', false);

        $userRu = User::factory()->create(['preferred_language' => 'ru']);
        $response = $this->actingAs($userRu)->get('/dashboard');
        $response->assertSee('dir="ltr"', false);
    }

    public function test_locale_persists_in_session_for_guest_users(): void
    {
        $this->get('/language/ru');

        $response = $this->get('/publications');

        $this->assertEquals('ru', app()->getLocale());
    }

    public function test_all_three_languages_have_translation_files(): void
    {
        $this->assertFileExists(lang_path('en.json'));
        $this->assertFileExists(lang_path('ru.json'));
        $this->assertFileExists(lang_path('he.json'));
    }

    public function test_translations_work_in_all_languages(): void
    {
        // Test English
        session(['locale' => 'en']);
        $response = $this->get('/publications');
        $response->assertSee('Publications');

        // Test Russian
        session(['locale' => 'ru']);
        $response = $this->get('/publications');
        $response->assertSee('Публикации');

        // Test Hebrew
        session(['locale' => 'he']);
        $response = $this->get('/publications');
        $response->assertSee('פרסומים');
    }
}
