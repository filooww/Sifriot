<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\FileRegistrationLog;
use App\Models\FolderScanJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FileRegistrationLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_file_registration_log_belongs_to_folder_scan_job(): void
    {
        $user = User::factory()->create();
        $scanJob = FolderScanJob::factory()->create(['user_id' => $user->id]);
        $log = FileRegistrationLog::factory()->create([
            'folder_scan_job_id' => $scanJob->id,
            'registered_by' => $user->id,
        ]);

        $this->assertInstanceOf(FolderScanJob::class, $log->folderScanJob);
        $this->assertEquals($scanJob->id, $log->folderScanJob->id);
    }

    public function test_failed_scope_filters_failed_registrations(): void
    {
        $user = User::factory()->create();
        FileRegistrationLog::factory()->create([
            'status' => 'processed',
            'registered_by' => $user->id,
        ]);
        FileRegistrationLog::factory()->create([
            'status' => 'failed',
            'registered_by' => $user->id,
        ]);
        FileRegistrationLog::factory()->create([
            'status' => 'failed',
            'registered_by' => $user->id,
        ]);

        $failedLogs = FileRegistrationLog::failed()->get();

        $this->assertCount(2, $failedLogs);
        $this->assertTrue($failedLogs->every(fn ($log) => $log->status === 'failed'));
    }
}
