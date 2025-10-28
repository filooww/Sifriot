<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Library Storage Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration controls the base path for library file storage.
    | The path is relative to the storage/app directory.
    |
    */

    'storage' => [
        'base_path' => env('LIBRARY_STORAGE_PATH', 'content'),
        'library_path' => env('LIBRARY_PATH', '/library'),
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Configuration
    |--------------------------------------------------------------------------
    |
    | These settings control file upload limits and allowed file types.
    | Max file size is in bytes (default: 524288000 = 500MB).
    |
    */

    'upload' => [
        'max_file_size' => env('LIBRARY_MAX_UPLOAD_SIZE', 524288000), // 500MB
        'allowed_mime_types' => [
            'application/pdf',
            'application/epub+zip',
            'application/epub',
            'text/plain',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/xml',  // FB2
            'application/x-fictionbook',  // FB2
            'image/vnd.djvu',
        ],
        'allowed_extensions' => ['pdf', 'epub', 'txt', 'doc', 'docx', 'fb2', 'djvu', 'xml'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Metadata Extraction Configuration
    |--------------------------------------------------------------------------
    |
    | Configure extraction behavior, timeouts, and retry logic.
    |
    */

    'extraction' => [
        'enabled' => env('METADATA_EXTRACTION_ENABLED', true),
        'timeout_seconds' => env('METADATA_EXTRACTION_TIMEOUT', 30),
        'max_retries' => env('METADATA_EXTRACTION_RETRIES', 3),
        'djvu_enable_ocr' => env('DJVU_ENABLE_OCR', false),  // Expensive, disabled by default
        'confidence_threshold' => env('EXTRACTION_CONFIDENCE_THRESHOLD', 0.6),  // Only show extractions > 60% confident
    ],

];
