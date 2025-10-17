<?php

namespace Tests\Feature;

use App\Models\Publication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicationListGuestAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_publication_list(): void
    {
        Publication::factory()->create(['title' => 'Test Publication']);

        $response = $this->get(route('publications.index'));

        $response->assertStatus(200);
        $response->assertSee('Test Publication');
    }

    public function test_guest_sees_register_cta_button(): void
    {
        $response = $this->get(route('publications.index'));

        $response->assertStatus(200);
        $response->assertSee('Register to access full content');
    }

    public function test_guest_does_not_see_delete_restore_buttons(): void
    {
        Publication::factory()->create(['title' => 'Test Publication']);

        $response = $this->get(route('publications.index'));

        $response->assertStatus(200);
        // Guests should only see View link, not Edit/Delete/Restore
        $response->assertSee(__('View'));
        // These should not appear as clickable actions (check for the specific links)
        $response->assertDontSee('text-green-600'); // Edit button class
        $response->assertDontSee('text-red-600'); // Delete button class
        $response->assertDontSee('wire:click="restorePublication'); // Restore button action
        $response->assertDontSee('wire:click="deletePublication'); // Delete button action
    }

    public function test_guest_does_not_see_admin_controls(): void
    {
        $response = $this->get(route('publications.index'));

        $response->assertStatus(200);
        $response->assertDontSee('Show Deleted');
        $response->assertDontSee('+ Add New');
    }

    public function test_authenticated_user_sees_full_controls(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        Publication::factory()->create(['title' => 'Test Publication']);

        $response = $this->actingAs($user)->get(route('publications.index'));

        $response->assertStatus(200);
        $response->assertSee('Show Deleted');
        $response->assertSee('+ Add New');
        $response->assertSee('Edit');
        $response->assertSee('Delete');
    }

    public function test_authenticated_user_does_not_see_register_cta(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get(route('publications.index'));

        $response->assertStatus(200);
        $response->assertDontSee('Register to access full content');
    }

    public function test_guest_only_sees_active_publications(): void
    {
        Publication::factory()->create(['title' => 'Active Publication']);
        $deleted = Publication::factory()->create(['title' => 'Deleted Publication']);
        $deleted->delete(); // Soft delete

        $response = $this->get(route('publications.index'));

        $response->assertStatus(200);
        $response->assertSee('Active Publication');
        $response->assertDontSee('Deleted Publication');
    }
}
