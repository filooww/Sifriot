<?php

declare(strict_types=1);

namespace Tests\Feature\Publications;

use App\Models\Publication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GlobalSearchTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function guest_user_can_search_and_sees_limited_results(): void
    {
        Publication::factory()->create(['title' => 'Science Fiction Book']);

        $response = $this->get(route('publications.index', ['search' => 'Science']));

        $response->assertStatus(200);
        $response->assertSee('Science Fiction Book');
    }

    /** @test */
    public function authenticated_user_can_search_and_sees_full_results(): void
    {
        $user = User::factory()->create();
        Publication::factory()->create(['title' => 'Advanced Laravel Tutorial']);

        $response = $this->actingAs($user)
            ->get(route('publications.index', ['search' => 'Laravel']));

        $response->assertStatus(200);
        $response->assertSee('Advanced Laravel Tutorial');
    }

    /** @test */
    public function search_returns_relevant_results_when_searching_by_title(): void
    {
        Publication::factory()->create(['title' => 'Space Exploration Book']);
        Publication::factory()->create(['title' => 'Ancient History Novel']);

        $response = $this->get(route('publications.index', ['search' => 'Space']));

        $response->assertStatus(200);
        $response->assertSee('Space Exploration Book');
        $response->assertDontSee('Ancient History Novel');
    }

    /** @test */
    public function search_with_empty_query_returns_all_publications(): void
    {
        Publication::factory()->count(5)->create();

        $response = $this->get(route('publications.index', ['search' => '']));

        $response->assertStatus(200);
    }

    /** @test */
    public function search_with_no_matches_displays_no_results_message(): void
    {
        Publication::factory()->create(['title' => 'Existing Book']);

        $response = $this->get(route('publications.index', ['search' => 'NonexistentKeyword']));

        $response->assertStatus(200);
        $response->assertDontSee('Existing Book');
    }

    /** @test */
    public function pagination_still_works_after_search_integration(): void
    {
        Publication::factory()->count(20)->create(['title' => 'Test Publication']);

        $response = $this->get(route('publications.index', ['search' => 'Test']));

        $response->assertStatus(200);
    }

    /** @test */
    public function search_excludes_soft_deleted_publications(): void
    {
        $publication = Publication::factory()->create(['title' => 'Deleted Publication']);
        $publication->delete();

        $response = $this->get(route('publications.index', ['search' => 'Deleted']));

        $response->assertStatus(200);
        $response->assertDontSee('Deleted Publication');
    }

    /** @test */
    public function search_performance_meets_requirement_with_many_results(): void
    {
        Publication::factory()->count(100)->create(['title' => 'Test Publication']);

        $startTime = microtime(true);
        $response = $this->get(route('publications.index', ['search' => 'Test']));
        $endTime = microtime(true);

        $executionTime = $endTime - $startTime;

        $this->assertLessThan(2.0, $executionTime, 'Search took longer than 2 seconds');
        $response->assertStatus(200);
    }
}
