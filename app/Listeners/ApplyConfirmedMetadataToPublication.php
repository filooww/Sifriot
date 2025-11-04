<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\MetadataConfirmed;
use App\Models\Publication;
use App\Models\Theme;
use Illuminate\Support\Facades\Log;

class ApplyConfirmedMetadataToPublication
{
    /**
     * Handle the event.
     */
    public function handle(MetadataConfirmed $event): void
    {
        $fileMetadata = $event->fileMetadata;

        // Parse file_id to get publication_id
        // Format: "{publication_id}-{filename}"
        $parts = explode('-', $fileMetadata->file_id, 2);
        if (count($parts) < 2) {
            Log::warning('Invalid file_id format for metadata application', [
                'file_id' => $fileMetadata->file_id,
            ]);

            return;
        }

        $publicationId = $parts[0];
        $publication = Publication::find($publicationId);

        if (! $publication) {
            Log::warning('Publication not found for metadata application', [
                'publication_id' => $publicationId,
                'file_id' => $fileMetadata->file_id,
            ]);

            return;
        }

        // Get confidence threshold
        $threshold = config('library.extraction.confidence_threshold', 0.6);
        $extractedData = $fileMetadata->extracted_data ?? [];
        $confidenceScores = $fileMetadata->confidence_scores ?? [];

        // Backup current values before updating
        $previousValues = [
            'author_names' => $publication->extracted_author_names,
            'publication_year' => $publication->extracted_publication_year,
            'publisher' => $publication->extracted_publisher,
            'isbn' => $publication->extracted_isbn,
            'doi' => $publication->extracted_doi,
        ];

        // Map extracted metadata to Publication (only high-confidence fields)
        $appliedFields = [];

        // Title
        if (isset($extractedData['title'])) {
            $titleConfidence = $confidenceScores['title'] ?? 0;
            if ($titleConfidence >= $threshold) {
                $titleValue = is_array($extractedData['title']) ? ($extractedData['title']['value'] ?? null) : $extractedData['title'];
                if ($titleValue) {
                    $publication->title = $titleValue;
                    $appliedFields[] = 'title';
                }
            }
        }

        // Authors
        if (isset($extractedData['authors']) && is_array($extractedData['authors'])) {
            $authorsConfidence = $confidenceScores['authors'] ?? 0;
            if ($authorsConfidence >= $threshold) {
                // Extract just the author names/values from the array of objects
                $authorNames = array_map(
                    fn ($author) => is_array($author) ? ($author['value'] ?? $author) : $author,
                    $extractedData['authors']
                );
                $publication->extracted_author_names = json_encode($authorNames);
                $appliedFields[] = 'authors';
            }
        }

        // Publication Year
        if (isset($extractedData['publication_year'])) {
            $yearConfidence = $confidenceScores['publication_year'] ?? 0;
            if ($yearConfidence >= $threshold) {
                $yearValue = is_array($extractedData['publication_year']) ? ($extractedData['publication_year']['value'] ?? null) : $extractedData['publication_year'];
                if ($yearValue) {
                    $publication->extracted_publication_year = (int) $yearValue;
                    $appliedFields[] = 'publication_year';
                }
            }
        }

        // Publisher
        if (isset($extractedData['publisher'])) {
            $publisherConfidence = $confidenceScores['publisher'] ?? 0;
            if ($publisherConfidence >= $threshold) {
                $publisherValue = is_array($extractedData['publisher']) ? ($extractedData['publisher']['value'] ?? null) : $extractedData['publisher'];
                if ($publisherValue) {
                    $publication->extracted_publisher = $publisherValue;
                    $appliedFields[] = 'publisher';
                }
            }
        }

        // ISBN
        if (isset($extractedData['isbn'])) {
            $isbnConfidence = $confidenceScores['isbn'] ?? 0;
            if ($isbnConfidence >= $threshold) {
                $isbnValue = is_array($extractedData['isbn']) ? ($extractedData['isbn']['value'] ?? null) : $extractedData['isbn'];
                if ($isbnValue) {
                    $publication->extracted_isbn = $isbnValue;
                    $appliedFields[] = 'isbn';
                }
            }
        }

        // DOI
        if (isset($extractedData['doi'])) {
            $doiConfidence = $confidenceScores['doi'] ?? 0;
            if ($doiConfidence >= $threshold) {
                $doiValue = is_array($extractedData['doi']) ? ($extractedData['doi']['value'] ?? null) : $extractedData['doi'];
                if ($doiValue) {
                    $publication->extracted_doi = $doiValue;
                    $appliedFields[] = 'doi';
                }
            }
        }

        // Genres - Sync with themes
        if (isset($extractedData['genres']) && is_array($extractedData['genres'])) {
            $genresConfidence = $confidenceScores['genres'] ?? 0;
            if ($genresConfidence >= $threshold && ! empty($extractedData['genres'])) {
                // Extract genre names and filter out empty values
                $genreNames = array_filter(
                    array_column($extractedData['genres'], 'value'),
                    fn ($name) => !empty(trim($name))
                );

                if (!empty($genreNames)) {
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
        }

        // Calculate average confidence score
        if (! empty($confidenceScores)) {
            $publication->metadata_confidence_avg = array_sum($confidenceScores) / count($confidenceScores);
        }

        // Set metadata tracking fields
        $publication->metadata_source = 'extracted';
        $publication->metadata_confirmed_at = now();
        $publication->metadata_previous_values = json_encode($previousValues);

        $publication->save();

        Log::channel('folder_scan')->info('Metadata applied to publication', [
            'publication_id' => $publication->id_publication,
            'file_metadata_id' => $fileMetadata->id,
            'applied_fields' => $appliedFields,
            'confidence_threshold' => $threshold,
        ]);
    }
}
