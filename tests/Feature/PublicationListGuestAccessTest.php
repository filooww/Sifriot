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

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Test Publication');
    }

    public function test_guest_sees_register_cta_button(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Register to access full content');
    }

    public function test_guest_does_not_see_delete_restore_buttons(): void
    {
        Publication::factory()->create(['title' => 'Test Publication']);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        // Public catalog shows grid view, not table with action buttons
        // No Edit/Delete/Restore buttons on public catalog
        $response->assertDontSee('wire:click="restorePublication'); // Restore button action
        $response->assertDontSee('wire:click="deletePublication'); // Delete button action
    }

    public function test_guest_does_not_see_admin_controls(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertDontSee('Show Deleted');
        $response->assertDontSee('Add New Publication');
    }

    public function test_authenticated_admin_sees_full_controls_on_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Publication::factory()->create(['title' => 'Test Publication']);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Show Deleted');
        $response->assertSee('Add New Publication');
        $response->assertSee('Delete');
    }

    public function test_authenticated_user_does_not_see_register_cta(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertStatus(200);
        $response->assertDontSee('Register to access full content');
    }

    public function test_guest_only_sees_active_publications(): void
    {
        Publication::factory()->create(['title' => 'Active Publication']);
        $deleted = Publication::factory()->create(['title' => 'Deleted Publication']);
        $deleted->delete(); // Soft delete

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Active Publication');
        $response->assertDontSee('Deleted Publication');
    }
}
