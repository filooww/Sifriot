<?php

declare(strict_types=1);

namespace App\Services\MetadataExtractors;

interface MetadataExtractorInterface
{
    /**
     * Extract metadata from a file.
     *
     * @param  string  $filePath  Absolute path to the file
     * @return ExtractedMetadata Data transfer object with extracted metadata
     */
    public function extract(string $filePath): ExtractedMetadata;
}
