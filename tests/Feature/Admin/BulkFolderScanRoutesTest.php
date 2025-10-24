<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\FolderScanJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkFolderScanRoutesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guest access to bulk-scan route is blocked.
     */
    public function test_guest_cannot_access_bulk_scan_route(): void
    {
        $response = $this->get(route('admin.bulk-scan'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test non-admin user access to bulk-scan route is blocked with 403.
     */
    public function test_non_admin_user_cannot_access_bulk_scan_route(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get(route('admin.bulk-scan'));

        $response->assertStatus(403);
    }

    /**
     * Test admin user can access bulk-scan route.
     */
    public function test_admin_user_can_access_bulk_scan_route(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.bulk-scan'));

        $response->assertStatus(200);
    }

    /**
     * Test guest access to scan-results route is blocked.
     */
    public function test_guest_cannot_access_scan_results_route(): void
    {
        $scanJob = FolderScanJob::factory()->create();

        $response = $this->get(route('admin.scan-results', ['scanJobId' => $scanJob->id]));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test non-admin user access to scan-results route is blocked with 403.
     */
    public function test_non_admin_user_cannot_access_scan_results_route(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $scanJob = FolderScanJob::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.scan-results', ['scanJobId' => $scanJob->id]));

        $response->assertStatus(403);
    }

    /**
     * Test admin user can access scan-results route.
     */
    public function test_admin_user_can_access_scan_results_route(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $scanJob = FolderScanJob::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.scan-results', ['scanJobId' => $scanJob->id]));

        $response->assertStatus(200);
    }
}
