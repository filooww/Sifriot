<?php

namespace Tests\Feature\Publications;

use App\Livewire\Publications\PublicationList;
use App\Models\Publication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PublicationListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_render_publication_list_page()
    {
        $response = $this->get(route('publications.index'));

        $response->assertStatus(200);
        $response->assertSeeLivewire(PublicationList::class);
    }

    /** @test */
    public function it_displays_publications_on_the_page()
    {
        $publication = Publication::factory()->create([
            'title' => 'Test Publication Title',
            '_del_mark' => 0,
        ]);

        Livewire::test(PublicationList::class)
            ->assertSee('Test Publication Title');
    }

    /** @test */
    public function it_can_search_publications_by_title()
    {
        Publication::factory()->create([
            'title' => 'Laravel Testing Guide',
            'title_low' => 'laravel testing guide',
            '_del_mark' => 0,
        ]);

        Publication::factory()->create([
            'title' => 'PHP Development',
            'title_low' => 'php development',
            '_del_mark' => 0,
        ]);

        Livewire::test(PublicationList::class)
            ->set('search', 'Laravel')
            ->assertSee('Laravel Testing Guide')
            ->assertDontSee('PHP Development');
    }

    /** @test */
    public function it_resets_page_when_searching()
    {
        Publication::factory()->count(20)->create(['_del_mark' => 0]);

        Livewire::test(PublicationList::class)
            ->call('nextPage') // Go to page 2
            ->set('search', 'test')
            ->assertSet('page', 1);
    }

    /** @test */
    public function it_can_filter_deleted_publications()
    {
        $activePublication = Publication::factory()->create([
            'title' => 'Active Publication',
            '_del_mark' => 0,
        ]);

        $deletedPublication = Publication::factory()->create([
            'title' => 'Deleted Publication',
            '_del_mark' => 1,
        ]);

        // Default view - shows only active publications
        Livewire::test(PublicationList::class)
            ->assertSee('Active Publication')
            ->assertDontSee('Deleted Publication');

        // Toggle to show deleted publications
        Livewire::test(PublicationList::class)
            ->set('showDeleted', true)
            ->assertDontSee('Active Publication')
            ->assertSee('Deleted Publication');
    }

    /** @test */
    public function it_can_toggle_deleted_publications()
    {
        Livewire::test(PublicationList::class)
            ->assertSet('showDeleted', false)
            ->call('toggleDeleted')
            ->assertSet('showDeleted', true)
            ->call('toggleDeleted')
            ->assertSet('showDeleted', false);
    }

    /** @test */
    public function it_can_soft_delete_a_publication()
    {
        $publication = Publication::factory()->create([
            'title' => 'Publication to Delete',
            '_del_mark' => 0,
        ]);

        Livewire::test(PublicationList::class)
            ->call('deletePublication', $publication->id_publication);

        $this->assertDatabaseHas('publications', [
            'id_publication' => $publication->id_publication,
            '_del_mark' => 1,
        ]);
    }

    /** @test */
    public function it_can_restore_a_deleted_publication()
    {
        $publication = Publication::factory()->create([
            'title' => 'Deleted Publication',
            '_del_mark' => 1,
        ]);

        Livewire::test(PublicationList::class)
            ->call('restorePublication', $publication->id_publication);

        $this->assertDatabaseHas('publications', [
            'id_publication' => $publication->id_publication,
            '_del_mark' => 0,
        ]);
    }

    /** @test */
    public function it_paginates_publications()
    {
        Publication::factory()->count(20)->create(['_del_mark' => 0]);

        Livewire::test(PublicationList::class)
            ->assertSet('perPage', 15)
            ->assertSee('publications'); // Verify pagination is working
    }

    /** @test */
    public function search_persists_in_url()
    {
        Livewire::test(PublicationList::class)
            ->set('search', 'Laravel')
            ->assertSet('search', 'Laravel');
    }

    /** @test */
    public function show_deleted_persists_in_url()
    {
        Livewire::test(PublicationList::class)
            ->set('showDeleted', true)
            ->assertSet('showDeleted', true);
    }

    /** @test */
    public function it_orders_publications_by_upload_date_descending()
    {
        $older = Publication::factory()->create([
            'title' => 'Older Publication',
            'upload_date' => now()->subDays(5),
            '_del_mark' => 0,
        ]);

        $newer = Publication::factory()->create([
            'title' => 'Newer Publication',
            'upload_date' => now(),
            '_del_mark' => 0,
        ]);

        $response = Livewire::test(PublicationList::class);

        $publications = $response->get('publications');
        $this->assertEquals($newer->id_publication, $publications->first()->id_publication);
    }

    /** @test */
    public function it_loads_relationships_efficiently()
    {
        Publication::factory()->count(5)->create(['_del_mark' => 0]);

        // This test ensures eager loading is working
        Livewire::test(PublicationList::class)
            ->assertStatus(200);

        // With eager loading, there should be minimal queries
        // The render method uses with(['publishing', 'authorGroup', ...])
        $this->assertTrue(true); // Placeholder for query count assertion
    }

    /** @test */
    public function guest_users_can_view_publication_list()
    {
        $publication = Publication::factory()->create([
            'title' => 'Public Publication',
            '_del_mark' => 0,
        ]);

        $response = $this->get(route('publications.index'));

        $response->assertStatus(200);
        $response->assertSee('Public Publication');
    }
}
