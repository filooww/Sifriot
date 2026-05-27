<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\File;
use App\Models\FileMetadata;
use App\Services\UniversalCoverExtractorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ExtractCoverForFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 60;

    public bool $deleteWhenMissing = true;

    public function __construct(
        public int $publicationId,
        public string $filePath,
        public string $fileName,
        public array $metadata = []
    ) {
        $this->onQueue('default');
    }

    public function handle(): void
    {
        try {
            Log::channel('folder_scan')->info('Starting cover extraction for file', [
                'publication_id' => $this->publicationId,
                'file_path' => $this->filePath,
                'file_name' => $this->fileName,
            ]);

            // Validate file exists
            if (!file_exists($this->filePath)) {
                Log::channel('folder_scan')->error('File not found for cover extraction', [
                    'publication_id' => $this->publicationId,
                    'file_path' => $this->filePath,
                ]);
                return;
            }

            // Extract or generate cover
            $coverExtractor = app(UniversalCoverExtractorService::class);
            $coverPath = $coverExtractor->extractOrGenerateCover(
                $this->filePath,
                $this->fileName,
                $this->metadata
            );

            if (!$coverPath) {
                Log::channel('folder_scan')->warning('Cover extraction failed', [
                    'publication_id' => $this->publicationId,
                    'file_name' => $this->fileName,
                ]);
                return;
            }

            // Save cover file
            $this->saveCoverFile($coverPath);

            Log::channel('folder_scan')->info('Cover extraction completed successfully', [
                'publication_id' => $this->publicationId,
                'file_name' => $this->fileName,
                'cover_path' => $coverPath,
            ]);

        } catch (\Exception $e) {
            Log::channel('folder_scan')->error('Cover extraction job failed', [
                'publication_id' => $this->publicationId,
                'file_path' => $this->filePath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    private function saveCoverFile(string $coverPath): void
    {
        try {
            // Generate unique filename for cover
            $extension = pathinfo($coverPath, PATHINFO_EXTENSION);
            $uniqueName = uniqid('cover_', true) . '_' . pathinfo($this->fileName, PATHINFO_FILENAME) . '.' . $extension;

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
                'file_description' => 'Auto-generated cover from ' . $this->fileName,
                'file_source' => 'bulk_generated',
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

    public function failed(\Throwable $exception): void
    {
        Log::channel('folder_scan')->error('Cover extraction job failed after all retries', [
            'publication_id' => $this->publicationId,
            'file_path' => $this->filePath,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);
    }
}