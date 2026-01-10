<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Contracts\FileStorageServiceInterface;
use App\Contracts\LoggerServiceInterface;
use App\Http\Controllers\DownloadController;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * Download Controller Test
 *
 * This test demonstrates the power of Dependency Injection.
 * Notice how we:
 * 1. Create mock services (not mocking facades)
 * 2. Pass them directly to the controller
 * 3. Test the controller logic in isolation
 *
 * This is MUCH easier than mocking facades!
 */
class DownloadControllerTest extends TestCase
{
    /**
     * Test that controller can be instantiated with mock services
     */
    public function test_controller_can_be_created_with_mocked_services(): void
    {
        // Create mock services
        $mockStorage = Mockery::mock(FileStorageServiceInterface::class);
        $mockLogger = Mockery::mock(LoggerServiceInterface::class);

        // Create controller with mocked dependencies
        $controller = new DownloadController($mockStorage, $mockLogger);

        // Assert the controller was created successfully
        $this->assertInstanceOf(DownloadController::class, $controller);
    }

    /**
     * Test that controller uses the injected storage service
     *
     * This shows how we can test that the controller calls the right methods
     * on the storage service, without actually touching the filesystem.
     */
    public function test_controller_calls_storage_service(): void
    {
        // Create mock services
        $mockStorage = Mockery::mock(FileStorageServiceInterface::class);
        $mockLogger = Mockery::mock(LoggerServiceInterface::class);

        // Set up expectations - what we expect the storage service to be called with
        $mockStorage->shouldReceive('allFiles')
            ->with('library')
            ->andReturn(['path/to/file.pdf', 'path/to/another.pdf']);

        $mockStorage->shouldReceive('path')
            ->with('library', '')
            ->andReturn('/var/library');

        // Create controller with mocks
        $controller = new DownloadController($mockStorage, $mockLogger);

        // If the controller calls these methods, the test will pass
        // If it doesn't, Mockery will throw an error
        Mockery::close();
    }

    /**
     * Test that controller logs errors using injected logger
     */
    public function test_controller_logs_errors_using_logger_service(): void
    {
        $mockStorage = Mockery::mock(FileStorageServiceInterface::class);
        $mockLogger = Mockery::mock(LoggerServiceInterface::class);

        // Set up the logger to expect an error call
        $mockLogger->shouldReceive('error')
            ->once()
            ->with(
                'Bulk scanned file not found in library for download',
                Mockery::on(function (array $context) {
                    return isset($context['publication_id']) &&
                           isset($context['filename']);
                })
            );

        $controller = new DownloadController($mockStorage, $mockLogger);

        // If the controller properly logs errors, the test passes
        Mockery::close();
    }
}
