<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\ExtractMetadataFromFile;
use App\Models\File;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ExtractMetadataForRegisteredFiles extends Command
{
    protected $signature = 'metadata:extract-all
                          {--limit=0 : Limit number of files (0 = all)}
                          {--content-type= : Filter by content type ID}
                          {--chunk-size=50 : Process in chunks}
                          {--force : Force re-extraction even if already extracted}';

    protected $description = 'Extract metadata for all registered files or re-extract existing ones';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $contentTypeId = $this->option('content-type');
        $chunkSize = (int) $this->option('chunk-size');
        $force = $this->option('force');

        // Find files to extract
        $query = File::query()
            ->join('publications', 'files.id_publication', '=', 'publications.id_publication')
            ->select('files.*', 'publications.content_type_id');

        // Filter by content type if specified
        if ($contentTypeId) {
            $query->where('publications.content_type_id', (int) $contentTypeId);
        }

        // If not forcing re-extraction, only get files without metadata
        if (! $force) {
            $query->leftJoin('file_metadatas', function ($join) {
                $join->on(\Illuminate\Support\Facades\DB::raw("CONCAT(files.id_publication, '-', files.file_name)"), '=', 'file_metadatas.file_id');
            })
                ->whereNull('file_metadatas.id');
        }

        // Apply limit
        if ($limit > 0) {
            $query->limit($limit);
        }

        $files = $query->get();
        $total = $files->count();

        if ($total === 0) {
            $this->info('✅ No files to process.');

            return self::SUCCESS;
        }

        $this->info("🔍 Processing {$total} files for metadata extraction...");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $queued = 0;
        $failed = 0;

        // Process in chunks to avoid memory issues
        $files->chunk($chunkSize)->each(function ($chunk) use (&$queued, &$failed, $bar) {
            foreach ($chunk as $file) {
                try {
                    // Get full path
                    $filePath = $file->file_source . '/' . $file->file_name;
                    $fullPath = Storage::disk('library')->path($filePath);

                    // Verify file still exists
                    if (! file_exists($fullPath)) {
                        $bar->advance();
                        $failed++;

                        continue;
                    }

                    // Dispatch extraction job
                    ExtractMetadataFromFile::dispatch(
                        "{$file->id_publication}-{$file->file_name}",
                        $fullPath,
                        (int) $file->content_type_id,
                        $file->mime_type
                    );

                    $queued++;
                } catch (\Exception $e) {
                    $this->error("\n❌ Error queueing file {$file->file_name}: {$e->getMessage()}");
                    $failed++;
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();

        // Summary
        $this->newLine();
        $this->info('═══════════════════════════════════════════');
        $this->info('📊 Metadata Extraction Summary');
        $this->info('═══════════════════════════════════════════');
        $this->info("✅ Queued: {$queued} files");
        if ($failed > 0) {
            $this->warn("⚠️  Failed: {$failed} files");
        }
        $this->info('═══════════════════════════════════════════');

        if ($queued > 0) {
            $this->newLine();
            $this->info('⏳ Extraction in progress. Monitor with:');
            $this->line('   <fg=green>php artisan queue:work -v</>');
            $this->newLine();
            $this->info('📋 View results in: <fg=green>/admin/files</> → <fg=green>Metadata Review</> tab');
            $this->newLine();
        }

        return $queued > 0 ? self::SUCCESS : self::FAILURE;
    }
}
