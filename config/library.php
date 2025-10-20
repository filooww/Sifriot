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
            'text/plain',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ],
        'allowed_extensions' => ['pdf', 'epub', 'txt', 'docx'],
    ],

];
