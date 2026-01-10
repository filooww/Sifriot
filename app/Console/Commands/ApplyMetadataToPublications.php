<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\FileMetadata;
use App\Models\Publication;
use App\Models\Theme;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ApplyMetadataToPublications extends Command
{
    protected $signature = 'metadata:apply-to-publications
                          {--confidence-threshold=0.6 : Minimum confidence threshold}
                          {--limit= : Limit number of publications to process}
                          {--force : Force re-apply even if already applied}';

    protected $description = 'Apply confirmed extracted metadata to Publication records';

    public function handle(): int
    {
        $threshold = (float) $this->option('confidence-threshold');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;
        $force = $this->option('force');

        $this->info('🔄 Starting metadata application to publications...');
        $this->newLine();

        // Find confirmed metadata that hasn't been applied
        $query = FileMetadata::where('status', 'confirmed');

        if (! $force) {
            // Only apply if Publication doesn't already have metadata confirmed
            $query->whereHas('publication', function ($q) {
                $q->whereNull('metadata_confirmed_at');
            });
        }

        if ($limit) {
            $query->limit($limit);
        }

        $metadatas = $query->get();
        $total = $metadatas->count();

        if ($total === 0) {
            $this->info('✅ No confirmed metadata to apply.');

            return self::SUCCESS;
        }

        $this->info("📋 Found {$total} metadata records to apply");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $applied = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($metadatas as $fileMetadata) {
            try {
                // Parse file_id to get publication_id
                $parts = explode('-', $fileMetadata->file_id, 2);
                if (count($parts) < 2) {
                    $skipped++;
                    $bar->advance();

                    continue;
                }

                $publicationId = $parts[0];
                $publication = Publication::find($publicationId);

                if (! $publication) {
                    $skipped++;
                    $bar->advance();

                    continue;
                }

                $extractedData = $fileMetadata->extracted_data ?? [];
                $confidenceScores = $fileMetadata->confidence_scores ?? [];

                // Backup current values
                $previousValues = [
                    'author_names' => $publication->extracted_author_names,
                    'publication_year' => $publication->extracted_publication_year,
                    'publisher' => $publication->extracted_publisher,
                ];

                // Apply only high-confidence fields
                $appliedFields = [];

                // Authors
                if (isset($extractedData['authors']) && is_array($extractedData['authors'])) {
                    $authorsConfidence = $confidenceScores['authors'] ?? 0;
                    if ($authorsConfidence >= $threshold) {
                        $publication->extracted_author_names = json_encode($extractedData['authors']);
                        $appliedFields[] = 'authors';
                    }
                }

                // Publication Year
                if (isset($extractedData['publication_year'])) {
                    $yearConfidence = $confidenceScores['publication_year'] ?? 0;
                    if ($yearConfidence >= $threshold) {
                        $publication->extracted_publication_year = (int) $extractedData['publication_year'];
                        $appliedFields[] = 'publication_year';
                    }
                }

                // Publisher
                if (isset($extractedData['publisher'])) {
                    $publisherConfidence = $confidenceScores['publisher'] ?? 0;
                    if ($publisherConfidence >= $threshold) {
                        $publication->extracted_publisher = $extractedData['publisher'];
                        $appliedFields[] = 'publisher';
                    }
                }

                // Genres - Sync with themes
                if (isset($extractedData['genres']) && is_array($extractedData['genres'])) {
                    $genresConfidence = $confidenceScores['genres'] ?? 0;
                    if ($genresConfidence >= $threshold && ! empty($extractedData['genres'])) {
                        $genreNames = array_column($extractedData['genres'], 'value');

                        // Find or create themes for each genre
                        $themeIds = [];
                        foreach ($genreNames as $genreName) {
                            $theme = Theme::firstOrCreate(
                                ['name' => $genreName],
                                ['name' => $genreName, 'description' => "Genre: {$genreName}"]
                            );
                            $themeIds[] = $theme->id_theme;
                        }

                        // Sync themes
                        if (! empty($themeIds)) {
                            $publication->themes()->sync($themeIds);
                            $appliedFields[] = 'genres (as themes)';
                        }
                    }
                }

                // Calculate average confidence
                if (! empty($confidenceScores)) {
                    $publication->metadata_confidence_avg = array_sum($confidenceScores) / count($confidenceScores);
                }

                // Set metadata tracking
                $publication->metadata_source = 'extracted';
                $publication->metadata_confirmed_at = now();
                $publication->metadata_previous_values = json_encode($previousValues);

                $publication->save();

                Log::channel('folder_scan')->info('Metadata applied to publication via command', [
                    'publication_id' => $publication->id_publication,
                    'file_metadata_id' => $fileMetadata->id,
                    'applied_fields' => $appliedFields,
                ]);

                $applied++;
            } catch (\Exception $e) {
                Log::error('Failed to apply metadata to publication', [
                    'file_metadata_id' => $fileMetadata->id,
                    'error' => $e->getMessage(),
                ]);
                $failed++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->newLine();

        // Summary
        $this->info('═══════════════════════════════════════════');
        $this->info('📊 Metadata Application Summary');
        $this->info('═══════════════════════════════════════════');
        $this->info("✅ Applied: {$applied} publications");
        if ($skipped > 0) {
            $this->warn("⏭️  Skipped: {$skipped} publications");
        }
        if ($failed > 0) {
            $this->error("❌ Failed: {$failed} publications");
        }
        $this->info('🎯 Confidence Threshold: '.($threshold * 100).'%');
        $this->info('═══════════════════════════════════════════');

        return $applied > 0 ? self::SUCCESS : self::FAILURE;
    }
}
