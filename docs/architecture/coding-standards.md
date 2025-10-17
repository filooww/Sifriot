# 8. Coding Standards

## 8.1 Existing Standards

- **PSR-12** via Laravel Pint
- **Strict typing** in service classes (`declare(strict_types=1)`)
- **Type hints** on all methods
- **PHPUnit** for testing
- **DocBlocks** for complex methods

## 8.2 Enhancement-Specific Patterns

**File Path Handling:**
```php
// ✅ GOOD
$basePath = config('library.storage.base_path');
$fullPath = Storage::disk('local')->path('content/' . $relativePath);

// ❌ BAD
$fullPath = '/mnt/library-storage/' . $relativePath;
```

**Queue Jobs:**
```php
// ✅ GOOD
ProcessMetadataExtraction::dispatch($publication)
    ->onQueue('metadata')
    ->onFailure(fn($e) => Log::channel('folder_scan')->error(...));
```

**Logging:**
```php
// ✅ GOOD
Log::channel('folder_scan')->info('Scan completed', [
    'job_id' => $scanJob->id,
    'files_registered' => $count,
]);
```

**Livewire Events:**
```php
// ✅ GOOD
$this->dispatch('scan-progress-updated', progress: $percent);
```

## 8.3 Critical Integration Rules

**Foreign Keys:**
```php
// ✅ GOOD - Explicit keys
$table->foreignId('publication_id')
    ->constrained('publications', 'id_publication');

// ❌ BAD - Assumes 'id'
$table->foreignId('publication_id')->constrained();
```

**Relationships:**
```php
// ✅ GOOD - Explicit foreign/local keys
public function publication(): BelongsTo
{
    return $this->belongsTo(Publication::class, 'publication_id', 'id_publication');
}
```

## 8.4 Pre-commit Hooks (Enforced)

```bash
#!/bin/sh
./vendor/bin/pint
php artisan test --testsuite=Unit --stop-on-failure
```

---
