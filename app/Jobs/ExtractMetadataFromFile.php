<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Events\MetadataExtracted;
use App\Models\File;
use App\Models\FileMetadata;
use App\Services\MetadataExtractors\MetadataExtractorFactory;
use App\Services\UniversalCoverExtractorService;
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
     * @param  int  $publicationId  Publication ID
     * @param  string  $filePath  Absolute path to file
     * @param  string|int  $contentTypeId  Content type ID
     * @param  string|null  $mimeType  MIME type of file
     */
    public function __construct(
        public int $publicationId,
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
            $fileName = basename($this->filePath);

            Log::channel('folder_scan')->info('Starting metadata extraction', [
                'publication_id' => $this->publicationId,
                'file_path' => $this->filePath,
                'content_type_id' => $this->contentTypeId,
            ]);

            // Step 1: Validate file exists and is readable
            if (! $this->validateFile()) {
                throw new \Exception('File does not exist or is not readable');
            }

            // Step 2: Get file info
            $mimeType = $this->mimeType ?? mime_content_type($this->filePath);

            // Step 3: Create or update FileMetadata record (mark as pending)
            $fileMetadata = FileMetadata::updateOrCreate(
                [
                    'publication_id' => $this->publicationId,
                    'file_name' => $fileName,
                ],
                [
                    'status' => 'pending',
                    'extracted_at' => null,
                ]
            );

            Log::channel('folder_scan')->info('FileMetadata record created', [
                'file_metadata_id' => $fileMetadata->id,
                'publication_id' => $this->publicationId,
                'file_name' => $fileName,
            ]);

            // Step 4: Create appropriate extractor
            $extractor = MetadataExtractorFactory::create($this->filePath, $mimeType);

            // Step 5: Extract metadata
            $extractedMetadata = $extractor->extract($this->filePath);

            // Step 6: Save extraction results
            $extractorClass = class_basename($extractor);

            $fileMetadata->update([
                'status' => $extractedMetadata->isEmpty() ? 'failed' : 'processed',
                'extracted_data' => $this->sanitizeUtf8($extractedMetadata->toArray()),
                'extraction_method' => $extractorClass,
                'extracted_at' => now(),
                'error_message' => $extractedMetadata->isEmpty() ? 'No metadata extracted' : null,
            ]);

            // Step 7: Extract or generate cover if metadata extraction was successful
            if ($fileMetadata->status === 'processed' && !$extractedMetadata->isEmpty()) {
                $this->processCoverExtraction($extractedMetadata->toArray());
            }

            // Step 8: Fire event
            MetadataExtracted::dispatch($fileMetadata);

            $duration = round((microtime(true) - $startTime) * 1000, 2);

            Log::channel('folder_scan')->info('Metadata extraction completed successfully', [
                'file_metadata_id' => $fileMetadata->id,
                'publication_id' => $this->publicationId,
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
     */
    public function failed(\Throwable $exception): void
    {
        Log::channel('folder_scan')->error('Metadata extraction job failed after all retries', [
            'publication_id' => $this->publicationId,
            'file_path' => $this->filePath,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);

        // Update FileMetadata with failed status
        try {
            $fileName = basename($this->filePath);
            $fileMetadata = FileMetadata::where('publication_id', $this->publicationId)
                ->where('file_name', $fileName)
                ->first();
            if ($fileMetadata) {
                $fileMetadata->update([
                    'status' => 'failed',
                    'error_message' => $exception->getMessage(),
                    'extracted_at' => now(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to update FileMetadata status after job failure', [
                'publication_id' => $this->publicationId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Validate that file exists and is readable.
     */
    private function validateFile(): bool
    {
        return file_exists($this->filePath) && is_readable($this->filePath);
    }

    /**
     * Handle extraction errors.
     *
     * @throws \Exception
     */
    private function handleError(\Exception $e, float $startTime): void
    {
        $duration = round((microtime(true) - $startTime) * 1000, 2);

        Log::channel('folder_scan')->error('Metadata extraction failed', [
            'publication_id' => $this->publicationId,
            'file_path' => $this->filePath,
            'error' => $e->getMessage(),
            'duration_ms' => $duration,
            'attempt' => $this->attempts(),
        ]);

        // Update FileMetadata with error
        try {
            $fileName = basename($this->filePath);
            $fileMetadata = FileMetadata::where('publication_id', $this->publicationId)
                ->where('file_name', $fileName)
                ->first();
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

    /**
     * Sanitize array to ensure valid UTF-8 encoding.
     */
    private function sanitizeUtf8(array $data): array
    {
        array_walk_recursive($data, function (&$item) {
            if (is_string($item)) {
                $item = mb_convert_encoding($item, 'UTF-8', 'UTF-8');
            }
        });
        return $data;
    }

    /**
     * Process cover extraction and generation.
     */
    private function processCoverExtraction(array $metadata): void
    {
        try {
            $fileName = basename($this->filePath);

            Log::channel('folder_scan')->info('Starting cover extraction', [
                'publication_id' => $this->publicationId,
                'file_path' => $this->filePath,
                'file_name' => $fileName,
            ]);

            $coverExtractor = app(UniversalCoverExtractorService::class);
            $coverPath = $coverExtractor->extractOrGenerateCover(
                $this->filePath,
                $fileName,
                $metadata
            );

            if ($coverPath) {
                $this->saveCoverFile($coverPath, $fileName, $metadata);

                Log::channel('folder_scan')->info('Cover extraction completed successfully', [
                    'publication_id' => $this->publicationId,
                    'file_name' => $fileName,
                    'cover_path' => $coverPath,
                ]);
            } else {
                Log::channel('folder_scan')->warning('Cover extraction failed', [
                    'publication_id' => $this->publicationId,
                    'file_name' => $fileName,
                ]);
            }
        } catch (\Exception $e) {
            Log::channel('folder_scan')->error('Cover extraction error', [
                'publication_id' => $this->publicationId,
                'file_path' => $this->filePath,
                'error' => $e->getMessage(),
            ]);
            // Don't fail the job if cover extraction fails
        }
    }

    /**
     * Save cover file to storage and create File record.
     */
    private function saveCoverFile(string $coverPath, string $originalFileName, array $metadata): void
    {
        try {
            // Generate unique filename for cover
            $extension = pathinfo($coverPath, PATHINFO_EXTENSION);
            $uniqueName = uniqid('cover_', true) . '_' . pathinfo($originalFileName, PATHINFO_FILENAME) . '.' . $extension;

            // Store cover in public disk
            $storagePath = 'covers/' . $uniqueName;
            Storage::disk('public')->put($storagePath, file_get_contents($coverPath));

            // Clean up temp file
            if (file_exists($coverPath)) {
                unlink($coverPath);
            }

            // Delete existing cover for this publication
            File::where('id_publication', $this->publicationId)
                ->where('file_type', 'cover')
                ->delete();

            // Create new File record for cover
            File::create([
                'id_publication' => $this->publicationId,
                'file_name' => $uniqueName,
                'file_name_low' => mb_strtolower($uniqueName),
                'file_description' => 'Auto-generated cover from ' . $originalFileName,
                'file_source' => 'auto_generated',
                'mime_type' => 'image/' . $extension,
                'file_size_bytes' => Storage::disk('public')->size($storagePath),
                'file_type' => 'cover',
                'file_path' => $storagePath,
                'ord_num' => 0,
            ]);

            Log::channel('folder_scan')->info('Cover file saved successfully', [
                'publication_id' => $this->publicationId,
                'storage_path' => $storagePath,
            ]);

        } catch (\Exception $e) {
            Log::channel('folder_scan')->error('Failed to save cover file', [
                'publication_id' => $this->publicationId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
