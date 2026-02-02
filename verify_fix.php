<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\FileMetadataService;
use App\Models\Publication;
use App\Models\FileMetadata;
use Illuminate\Support\Facades\Log;

echo "Verification: Simulating Dashboard Auto-Healing...\n";

// 1. Verify we have orphans primarily
$orphanCount = Publication::whereDoesntHave('fileMetadata')->where('status', '!=', 'deleted')->count();
echo "Initial Orphan Count: $orphanCount\n";

if ($orphanCount == 0) {
    echo "No orphans to fix! (Did it already run?)\n";
    // Force delete one to test?
    echo "Creating a test orphan...\n";
    $files = FileMetadata::first();
    if ($files) {
        $files->delete();
        echo "Deleted metadata for file: " . $files->file_name . "\n";
    }
}

// 2. Simulate Service Call (as Dashboard would)
$service = new FileMetadataService();
$orphans = Publication::whereDoesntHave('fileMetadata')
    ->where('status', '!=', 'deleted')
    ->get();

echo "Found Orphans to fix: " . $orphans->count() . "\n";

if ($orphans->isNotEmpty()) {
    $count = $service->syncMetadataForPublications($orphans);
    echo "Service synced: $count items\n";
}

// 3. Verify Result
$finalOrphanCount = Publication::whereDoesntHave('fileMetadata')->where('status', '!=', 'deleted')->count();
echo "Final Orphan Count: $finalOrphanCount\n";

if ($finalOrphanCount == 0) {
    echo "SUCCESS: All orphans fixed.\n";
} else {
    echo "FAILURE: $finalOrphanCount orphans remain.\n";
}
