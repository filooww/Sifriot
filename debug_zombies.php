<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FileMetadata;
use App\Models\Publication;
use Illuminate\Support\Facades\DB;

// 1. Count total metadata
$total = FileMetadata::count();
echo "Total FileMetadata: $total\n";

// 2. Run the exact query logic used in likely-to-fail filter
$hiddenByFilter = FileMetadata::whereExists(function ($subQuery) {
    $subQuery->select(DB::raw(1))
        ->from('publications')
        ->whereRaw('publications.id_publication = CAST(SUBSTRING_INDEX(file_metadatas.file_id, "-", 1) AS UNSIGNED)')
        ->whereNull('deleted_at');
})->count();

echo "Visible by Filter (Non-deleted parents): $hiddenByFilter\n";
echo "Should be Hidden (Zombies): " . ($total - $hiddenByFilter) . "\n";

// 3. Inspect the zombies
$zombies = FileMetadata::whereNotExists(function ($subQuery) {
    $subQuery->select(DB::raw(1))
        ->from('publications')
        ->whereRaw('publications.id_publication = CAST(SUBSTRING_INDEX(file_metadatas.file_id, "-", 1) AS UNSIGNED)')
        ->whereNull('deleted_at');
})->limit(5)->get();

if ($zombies->isEmpty()) {
    echo "No zombies found by query!\n";
} else {
    echo "Sample Zombies found:\n";
    foreach ($zombies as $z) {
        $parts = explode('-', $z->file_id);
        $pubId = $parts[0] ?? '?';
        $pub = Publication::withTrashed()->find($pubId);
        
        echo "- Meta ID: {$z->id}, FileID: {$z->file_id}, PubID: {$pubId} ";
        if ($pub) {
            echo "[Pub Found: ID {$pub->id_publication}, DeletedAt: " . ($pub->deleted_at ?? 'NULL') . "]\n";
        } else {
            echo "[Pub NOT Found]\n";
        }
    }
}
