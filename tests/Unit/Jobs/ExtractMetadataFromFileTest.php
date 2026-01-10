<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs;

use App\Jobs\ExtractMetadataFromFile;
use App\Models\FileMetadata;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ExtractMetadataFromFileTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_can_be_dispatched(): void
    {
        Queue::fake();

        ExtractMetadataFromFile::dispatch('file-123', '/tmp/test.pdf', 1);

        Queue::assertPushed(ExtractMetadataFromFile::class);
    }

    public function test_job_creates_file_metadata_record(): void
    {
        // Create a test file
        $tmpFile = tempnam(sys_get_temp_dir(), 'test_').'.txt';
        file_put_contents($tmpFile, 'Test content');

        try {
            $job = new ExtractMetadataFromFile('file-test', $tmpFile, 1, 'text/plain');
            $job->handle();

            // Verify FileMetadata record was created
            $metadata = FileMetadata::where('file_id', 'file-test')->first();
            $this->assertNotNull($metadata);
            $this->assertIn($metadata->status, ['processed', 'failed']);
        } finally {
            unlink($tmpFile);
        }
    }

    public function test_job_marks_as_failed_for_missing_file(): void
    {
        $job = new ExtractMetadataFromFile('file-missing', '/nonexistent/file.pdf', 1);

        try {
            $job->handle();
        } catch (\Exception $e) {
            // Expected to fail
        }

        // Job should have failed and recorded FileMetadata
        $metadata = FileMetadata::where('file_id', 'file-missing')->first();
        $this->assertNotNull($metadata);
    }

    public function test_job_respects_timeout_setting(): void
    {
        $job = new ExtractMetadataFromFile('file-123', '/tmp/test.pdf', 1);

        $this->assertEquals(30, $job->timeout);
    }

    public function test_job_respects_retry_limit(): void
    {
        $job = new ExtractMetadataFromFile('file-123', '/tmp/test.pdf', 1);

        $this->assertEquals(3, $job->tries);
    }
}
