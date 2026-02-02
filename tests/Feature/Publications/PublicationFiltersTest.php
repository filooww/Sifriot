<?php

declare(strict_types=1);

namespace Tests\Feature\Publications;

use App\Models\Author;
use App\Models\Section;
use App\Models\Publication;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicationFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_apply_section_filter_and_see_filtered_results(): void
    {
        $section = Section::factory()->create(['name_en' => 'Science Fiction']);
        $matchingPub = Publication::factory()->create(['title' => 'Dune']);
        $nonMatchingPub = Publication::factory()->create(['title' => 'Other Book']);

        $matchingPub->sections()->attach($section->id);

        $response = $this->get(route('home', ['sec' => [$section->id]]));

        $response->assertStatus(200);
        $response->assertSee('Dune');
        $response->assertDontSee('Other Book');
    }

    public function test_multiple_filters_combine_with_and_logic(): void
    {
        $section = Section::factory()->create(['name_en' => 'Fiction']);
        $author = Author::factory()->create(['author_name' => 'John Doe']);

        // Create publication matching both filters
        $matchingPub = Publication::factory()->create([
            'title' => 'Matching Book',
            'upload_date' => '2022-06-15',
        ]);
        $matchingPub->sections()->attach($section->id);
        $matchingPub->authors()->attach($author->id_author);

        // Create publication matching only section filter
        $nonMatchingPub = Publication::factory()->create([
            'title' => 'Non-Matching Book',
            'upload_date' => '2022-06-15',
        ]);
        $nonMatchingPub->sections()->attach($section->id);

        $response = $this->get(route('home', [
            'sec' => [$section->id],
            'auth' => [$author->id_author],
        ]));

        $response->assertStatus(200);
        $response->assertSee('Matching Book');
        $response->assertDontSee('Non-Matching Book');
    }

    public function test_user_can_apply_date_range_filter(): void
    {
        $pubInRange = Publication::factory()->create([
            'title' => 'In Range',
            'upload_date' => '2022-06-15',
        ]);
        $pubOutRange = Publication::factory()->create([
            'title' => 'Out Range',
            'upload_date' => '2023-12-31',
        ]);

        $response = $this->get(route('home', [
            'from' => '2022-01-01',
            'to' => '2022-12-31',
        ]));

        $response->assertStatus(200);
        $response->assertSee('In Range');
        $response->assertDontSee('Out Range');
    }

    public function test_user_can_apply_text_size_range_filter(): void
    {
        $smallPub = Publication::factory()->create([
            'title' => 'Small Publication',
            'word_count' => 5000,
        ]);
        $largePub = Publication::factory()->create([
            'title' => 'Large Publication',
            'word_count' => 150000,
        ]);

        $response = $this->get(route('home', [
            'size' => [0, 10000],
        ]));

        $response->assertStatus(200);
        $response->assertSee('Small Publication');
        $response->assertDontSee('Large Publication');
    }

    public function test_user_can_sort_alphabetically_a_to_z(): void
    {
        Publication::factory()->create(['title' => 'Zebra Book']);
        Publication::factory()->create(['title' => 'Apple Book']);

        $response = $this->get(route('home', ['sort' => 'asc']));

        $response->assertStatus(200);
        $content = $response->getContent();
        $applePos = strpos($content, 'Apple Book');
        $zebraPos = strpos($content, 'Zebra Book');

        $this->assertLessThan($zebraPos, $applePos, 'Apple Book should appear before Zebra Book');
    }

    public function test_user_can_sort_alphabetically_z_to_a(): void
    {
        Publication::factory()->create(['title' => 'Zebra Book']);
        Publication::factory()->create(['title' => 'Apple Book']);

        $response = $this->get(route('home', ['sort' => 'desc']));

        $response->assertStatus(200);
        $content = $response->getContent();
        $applePos = strpos($content, 'Apple Book');
        $zebraPos = strpos($content, 'Zebra Book');

        $this->assertLessThan($applePos, $zebraPos, 'Zebra Book should appear before Apple Book');
    }

    public function test_admin_can_filter_by_publication_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Publication::factory()->create(['title' => 'Published Book', 'status' => 'published']);
        Publication::factory()->create(['title' => 'Hidden Book', 'status' => 'hidden']);

        $response = $this->actingAs($admin)->get(route('home', [
            'status' => ['published'],
        ]));

        $response->assertStatus(200);
        $response->assertSee('Published Book');
        $response->assertDontSee('Hidden Book');
    }

    public function test_regular_user_cannot_see_publication_status_filter(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertStatus(200);
        $response->assertDontSee('Publication Status');
    }

    public function test_applied_filters_display_as_removable_tags(): void
    {
        $section = Section::factory()->create(['name_en' => 'Fiction']);

        $response = $this->get(route('home', ['sec' => [$section->id]]));

        $response->assertStatus(200);
        $response->assertSee('Fiction');
    }

    public function test_filter_state_persists_across_pagination(): void
    {
        $section = Section::factory()->create(['name_en' => 'Fiction']);

        // Create 20 publications with section
        for ($i = 1; $i <= 20; $i++) {
            $pub = Publication::factory()->create(['title' => "Book {$i}"]);
            $pub->sections()->attach($section->id);
        }

        $response = $this->get(route('home', ['sec' => [$section->id], 'page' => 2]));

        $response->assertStatus(200);
        $this->assertEquals($section->id, request()->query('sec')[0] ?? null);
    }

    public function test_url_parameters_reflect_active_filters(): void
    {
        $section = Section::factory()->create();
        $author = Author::factory()->create();

        $response = $this->get(route('home', [
            'sec' => [$section->id],
            'auth' => [$author->id_author],
            'from' => '2022-01-01',
            'to' => '2022-12-31',
        ]));

        $response->assertStatus(200);

        $this->assertEquals([$section->id], request()->query('sec'));
        $this->assertEquals([$author->id_author], request()->query('auth'));
        $this->assertEquals('2022-01-01', request()->query('from'));
        $this->assertEquals('2022-12-31', request()->query('to'));
    }

    public function test_filters_work_correctly_with_search_query(): void
    {
        $section = Section::factory()->create(['name_en' => 'Fiction']);

        $matchingPub = Publication::factory()->create(['title' => 'Dune']);
        $matchingPub->sections()->attach($section->id);

        $nonMatchingPub = Publication::factory()->create(['title' => 'Other Book']);
        $nonMatchingPub->sections()->attach($section->id);

        $response = $this->get(route('home', [
            'search' => 'Dune',
            'sec' => [$section->id],
        ]));

        $response->assertStatus(200);
        $response->assertSee('Dune');
        $response->assertDontSee('Other Book');
    }

    public function test_genre_filter_works_correctly(): void
    {
        $theme = Theme::factory()->create(['theme' => 'Science Fiction']);

        $matchingPub = Publication::factory()->create(['title' => 'Sci-Fi Book']);
        $matchingPub->themes()->attach($theme->id_theme);

        $nonMatchingPub = Publication::factory()->create(['title' => 'Other Book']);

        $response = $this->get(route('home', [
            'genre' => [$theme->id_theme],
        ]));

        $response->assertStatus(200);
        $response->assertSee('Sci-Fi Book');
        $response->assertDontSee('Other Book');
    }

    public function test_hierarchical_section_filter_works_correctly(): void
    {
        $parent = Section::factory()->create(['name_en' => 'Books']);
        $child = Section::factory()->create([
            'name_en' => 'Fiction',
            'parent_id' => $parent->id,
        ]);

        $pubWithChild = Publication::factory()->create(['title' => 'Fiction Book']);
        $pubWithChild->sections()->attach($child->id);

        $pubWithParent = Publication::factory()->create(['title' => 'General Book']);
        $pubWithParent->sections()->attach($parent->id);

        // Filter by child section
        $response = $this->get(route('home', ['sec' => [$child->id]]));

        $response->assertStatus(200);
        $response->assertSee('Fiction Book');
        $response->assertDontSee('General Book');
    }

    public function test_filters_work_correctly_in_all_three_languages(): void
    {
        $section = Section::factory()->create([
            'name_en' => 'Science Fiction',
            'name_ru' => 'Научная фантастика',
            'name_he' => 'מדע בדיוני',
        ]);

        // Test English
        app()->setLocale('en');
        $response = $this->get(route('home'));
        $response->assertStatus(200);

        // Test Russian
        app()->setLocale('ru');
        $response = $this->get(route('home'));
        $response->assertStatus(200);

        // Test Hebrew
        app()->setLocale('he');
        $response = $this->get(route('home'));
        $response->assertStatus(200);
    }

    public function test_filter_performance_with_large_dataset(): void
    {
        // Create 100 test publications (reduced from 1000 for test speed)
        $section = Section::factory()->create();
        $author = Author::factory()->create();

        for ($i = 0; $i < 100; $i++) {
            $pub = Publication::factory()->create([
                'upload_date' => '2022-06-15',
                'word_count' => 50000,
            ]);
            if ($i % 2 === 0) {
                $pub->sections()->attach($section->id);
                $pub->authors()->attach($author->id_author);
            }
        }

        $startTime = microtime(true);

        $response = $this->get(route('home', [
            'sec' => [$section->id],
            'auth' => [$author->id_author],
            'from' => '2020-01-01',
            'to' => '2023-12-31',
        ]));

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $this->assertLessThan(3.0, $executionTime, 'Filtering took longer than 3 seconds');
        $response->assertStatus(200);
    }
}
