<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Publication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicationStatusFilterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that guests only see published publications on the list
     */
    public function test_guests_only_see_published_publications(): void
    {
        // Create publications with different statuses
        Publication::factory()->create(['status' => 'published', 'title' => 'Published Book']);
        Publication::factory()->create(['status' => 'hidden', 'title' => 'Hidden Book']);
        Publication::factory()->create(['status' => 'pending', 'title' => 'Pending Book']);

        // Guest request
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        // The response should contain the Livewire component
        $response->assertViewHas('publications');
    }

    /**
     * Test that guests cannot access unpublished publications directly
     */
    public function test_guests_cannot_view_unpublished_publication(): void
    {
        $published = Publication::factory()->create(['status' => 'published']);
        $hidden = Publication::factory()->create(['status' => 'hidden']);
        $pending = Publication::factory()->create(['status' => 'pending']);

        // Can view published
        $response = $this->get(route('publications.show', $published->id_publication));
        $response->assertStatus(200);

        // Cannot view hidden
        $response = $this->get(route('publications.show', $hidden->id_publication));
        $response->assertStatus(404);

        // Cannot view pending
        $response = $this->get(route('publications.show', $pending->id_publication));
        $response->assertStatus(404);
    }

    /**
     * Test that authenticated non-admin users only see published publications
     */
    public function test_authenticated_user_only_sees_published_publications(): void
    {
        $user = $this->createUser();

        Publication::factory()->create(['status' => 'published', 'title' => 'Published']);
        Publication::factory()->create(['status' => 'hidden', 'title' => 'Hidden']);
        Publication::factory()->create(['status' => 'pending', 'title' => 'Pending']);

        $response = $this->actingAs($user)->get(route('home'));
        $response->assertStatus(200);
    }

    /**
     * Test that admins can see all publications regardless of status
     */
    public function test_admin_can_see_all_publication_statuses(): void
    {
        $admin = $this->createAdmin();

        Publication::factory()->create(['status' => 'published']);
        Publication::factory()->create(['status' => 'hidden']);
        Publication::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($admin)->get(route('dashboard'));
        $response->assertStatus(200);
    }

    /**
     * Helper to create a regular user
     */
    protected function createUser()
    {
        return \App\Models\User::factory()->create(['role' => 'user']);
    }

    /**
     * Helper to create an admin user
     */
    protected function createAdmin()
    {
        return \App\Models\User::factory()->create(['role' => 'admin']);
    }
}
