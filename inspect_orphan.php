<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Publication;
use Illuminate\Support\Facades\DB;

$id = 4;
$pub = Publication::with('files')->find($id);

if (!$pub) {
    echo "Publication $id not found.\n";
    exit;
}

echo "Publication: " . $pub->title . "\n";
echo "Files count: " . $pub->files->count() . "\n";

foreach ($pub->files as $file) {
    echo " - File: " . $file->file_name . " (Type: " . $file->file_type . ", Size: " . $file->file_size_bytes . ")\n";
}

// Also check registration logs
$logs = DB::table('file_registration_logs')->where('publication_id', $id)->get();
echo "Registration Logs: " . $logs->count() . "\n";
foreach ($logs as $log) {
    echo " - Log: " . $log->file_path . " (" . $log->created_at . ")\n";
}
