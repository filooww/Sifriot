<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Publication;
use App\Models\FileMetadata;

echo "Checking for orphans (ALL statuses)...\n";

// Get ALL publications
$publications = Publication::all(['id_publication', 'title', 'status', 'upload_date']);
$publicationIds = $publications->pluck('id_publication');

echo "Total Publications: " . $publications->count() . "\n";

if ($publications->count() > 0) {
    echo "Statuses found:\n";
    foreach ($publications->groupBy('status') as $status => $items) {
        echo "  - $status: " . $items->count() . "\n";
    }
}

// Get all unique publication IDs extracted from FileMetadata file_ids
// file_id format: "123-filename.ext"
$metadataPublicationIds = FileMetadata::all()->map(function($meta) {
    if (!$meta->file_id) return 0;
    $parts = explode('-', $meta->file_id);
    return (int) $parts[0];
})->unique();

echo "Publications with Metadata: " . $metadataPublicationIds->count() . "\n";

// Find orphans
$orphans = $publicationIds->diff($metadataPublicationIds);

echo "Orphaned Publications (No Metadata at all): " . $orphans->count() . "\n";

if ($orphans->count() > 0) {
    echo "Sample Orphans:\n";
    foreach ($orphans->take(5) as $id) {
        $p = $publications->firstWhere('id_publication', $id);
        echo "- ID: $id, Title: " . $p->title . " [Status: " . $p->status . "]\n";
    }
}
