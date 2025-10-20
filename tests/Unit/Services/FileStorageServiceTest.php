<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\FileRegistrationLog;
use App\Models\User;
use App\Services\FileStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileStorageServiceTest extends TestCase
{
    use RefreshDatabase;

    protected FileStorageService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new FileStorageService;
    }

    public function test_browse_folder_returns_files_and_folders(): void
    {
        Storage::fake('local');

        // Create folder structure
        Storage::disk('local')->makeDirectory('content/books');
        Storage::disk('local')->makeDirectory('content/magazines');
        Storage::disk('local')->put('content/books/file1.pdf', 'content');
        Storage::disk('local')->put('content/books/file2.txt', 'content');

        $result = $this->service->browseFolder('content');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('folders', $result);
        $this->assertArrayHasKey('files', $result);
        $this->assertGreaterThanOrEqual(2, count($result['folders'])); // books, magazines
    }

    public function test_get_file_metadata_extracts_title_from_filename(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('content/books/sample-book-title.pdf', 'content');

        $metadata = $this->service->getFileMetadata('content/books/sample-book-title.pdf');

        $this->assertArrayHasKey('suggested_title', $metadata);
        $this->assertEquals('Sample Book Title', $metadata['suggested_title']);
        $this->assertArrayHasKey('content_type_id', $metadata);
    }

    public function test_validate_file_path_rejects_path_traversal(): void
    {
        Storage::fake('local');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid file path');

        // Attempt path traversal
        $this->service->validateFilePath('../../../etc/passwd');
    }

    public function test_get_file_content_reads_file_successfully(): void
    {
        Storage::fake('local');

        $content = 'Test file content';
        Storage::disk('local')->put('content/books/test.txt', $content);

        $result = $this->service->getFileContent('content/books/test.txt');

        $this->assertEquals($content, $result);
    }

    public function test_file_is_registered_check_works(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('content/books/registered.pdf', 'content');

        $filePath = Storage::disk('local')->path('content/books/registered.pdf');
        $user = User::factory()->create(['role' => 'admin']);

        // Create registration log
        FileRegistrationLog::create([
            'file_path' => $filePath,
            'registration_source' => 'manual_registration',
            'status' => 'processed',
            'registered_by' => $user->id,
        ]);

        $result = $this->service->browseFolder('content/books');

        $registeredFile = collect($result['files'])->firstWhere('name', 'registered.pdf');

        $this->assertTrue($registeredFile['is_registered']);
    }

    public function test_browse_folder_handles_large_file_count(): void
    {
        Storage::fake('local');

        // Create 1500 files to test pagination
        Storage::disk('local')->makeDirectory('content/books');
        for ($i = 0; $i < 100; $i++) {
            Storage::disk('local')->put("content/books/file{$i}.pdf", 'content');
        }

        $startTime = microtime(true);

        $result = $this->service->browseFolder('content/books');

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // Verify performance: should complete in under 2 seconds
        $this->assertLessThan(2.0, $executionTime, 'Browse folder took longer than 2 seconds');
        $this->assertCount(100, $result['files']);
    }
}
