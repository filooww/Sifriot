<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Publication;
use App\Models\FileMetadata;

echo "Checking for orphans...\n";

// Get all publication IDs (published only? or all? User said 'root page', so published)
$publications = Publication::where('status', 'published')->get(['id_publication', 'title']);
$publicationIds = $publications->pluck('id_publication');

echo "Total Published Publications: " . $publications->count() . "\n";

// Get all unique publication IDs extracted from FileMetadata file_ids
$metadataPublicationIds = FileMetadata::all()->map(function($meta) {
    return (int) explode('-', $meta->file_id)[0];
})->unique();

echo "Publications with Metadata: " . $metadataPublicationIds->count() . "\n";

// Find orphans
$orphans = $publicationIds->diff($metadataPublicationIds);

echo "Orphaned Publications (Visible on Root but Missing Metadata): " . $orphans->count() . "\n";

if ($orphans->count() > 0) {
    echo "Sample Orphans:\n";
    foreach ($orphans->take(5) as $id) {
        $p = $publications->firstWhere('id_publication', $id);
        echo "- ID: $id, Title: " . $p->title . "\n";
    }
}
