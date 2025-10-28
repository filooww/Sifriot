<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Events\MetadataExtracted;
use App\Models\FileMetadata;
use App\Services\MetadataExtractors\MetadataExtractorFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ExtractMetadataFromFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     */
    public int $maxExceptions = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 30;

    /**
     * Delete the job if its models no longer exist.
     */
    public bool $deleteWhenMissing = true;

    /**
     * Create a new job instance.
     *
     * @param string $fileId File identifier
     * @param string $filePath Absolute path to file
     * @param string|int $contentTypeId Content type ID
     * @param string|null $mimeType MIME type of file
     */
    public function __construct(
        public string $fileId,
        public string $filePath,
        public int|string $contentTypeId,
        public ?string $mimeType = null
    ) {
        $this->onQueue('default');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $startTime = microtime(true);

        try {
            Log::channel('folder_scan')->info('Starting metadata extraction', [
                'file_id' => $this->fileId,
                'file_path' => $this->filePath,
                'content_type_id' => $this->contentTypeId,
            ]);

            // Step 1: Validate file exists and is readable
            if (!$this->validateFile()) {
                throw new \Exception('File does not exist or is not readable');
            }

            // Step 2: Get file info
            $fileName = basename($this->filePath);
            $mimeType = $this->mimeType ?? mime_content_type($this->filePath);

            // Step 3: Create or update FileMetadata record (mark as pending)
            $fileMetadata = FileMetadata::updateOrCreate(
                ['file_id' => $this->fileId],
                [
                    'file_name' => $fileName,
                    'status' => 'pending',
                    'extracted_at' => null,
                ]
            );

            Log::channel('folder_scan')->info('FileMetadata record created', [
                'file_metadata_id' => $fileMetadata->id,
                'file_id' => $this->fileId,
            ]);

            // Step 4: Create appropriate extractor
            $extractor = MetadataExtractorFactory::create($this->filePath, $mimeType);

            // Step 5: Extract metadata
            $extractedMetadata = $extractor->extract($this->filePath);

            // Step 6: Save extraction results
            $extractorClass = class_basename($extractor);

            $fileMetadata->update([
                'status' => $extractedMetadata->isEmpty() ? 'failed' : 'processed',
                'extracted_data' => $extractedMetadata->toArray(),
                'extraction_method' => $extractorClass,
                'confidence_scores' => $extractedMetadata->getConfidenceScores(),
                'extracted_at' => now(),
                'error_message' => $extractedMetadata->isEmpty() ? 'No metadata extracted' : null,
            ]);

            // Step 7: Fire event
            MetadataExtracted::dispatch($fileMetadata);

            $duration = round((microtime(true) - $startTime) * 1000, 2);

            Log::channel('folder_scan')->info('Metadata extraction completed successfully', [
                'file_metadata_id' => $fileMetadata->id,
                'file_id' => $this->fileId,
                'extractor' => $extractorClass,
                'status' => $fileMetadata->status,
                'duration_ms' => $duration,
                'has_title' => (bool) $extractedMetadata->getTitle(),
                'author_count' => count($extractedMetadata->getAuthors()),
            ]);
        } catch (\Exception $e) {
            $this->handleError($e, $startTime);
        }
    }

    /**
     * Handle job failure.
     *
     * @param \Throwable $exception
     */
    public function failed(\Throwable $exception): void
    {
        Log::channel('folder_scan')->error('Metadata extraction job failed after all retries', [
            'file_id' => $this->fileId,
            'file_path' => $this->filePath,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);

        // Update FileMetadata with failed status
        try {
            $fileMetadata = FileMetadata::where('file_id', $this->fileId)->first();
            if ($fileMetadata) {
                $fileMetadata->update([
                    'status' => 'failed',
                    'error_message' => $exception->getMessage(),
                    'extracted_at' => now(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to update FileMetadata status after job failure', [
                'file_id' => $this->fileId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Validate that file exists and is readable.
     *
     * @return bool
     */
    private function validateFile(): bool
    {
        return file_exists($this->filePath) && is_readable($this->filePath);
    }

    /**
     * Handle extraction errors.
     *
     * @param \Exception $e
     * @param float $startTime
     * @throws \Exception
     */
    private function handleError(\Exception $e, float $startTime): void
    {
        $duration = round((microtime(true) - $startTime) * 1000, 2);

        Log::channel('folder_scan')->error('Metadata extraction failed', [
            'file_id' => $this->fileId,
            'file_path' => $this->filePath,
            'error' => $e->getMessage(),
            'duration_ms' => $duration,
            'attempt' => $this->attempts(),
        ]);

        // Update FileMetadata with error
        try {
            $fileMetadata = FileMetadata::where('file_id', $this->fileId)->first();
            if ($fileMetadata) {
                $fileMetadata->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'extracted_at' => now(),
                ]);
            }
        } catch (\Exception $ex) {
            Log::error('Failed to update FileMetadata on error', ['error' => $ex->getMessage()]);
        }

        // Rethrow to trigger retry logic
        throw $e;
    }
}
