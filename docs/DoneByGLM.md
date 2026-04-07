

## FULL CHANGE LOG

### PHASE 1: Zero-risk deletions
| What | Files |
|------|-------|
| Empty migration | `2025_11_21_200435_make_themes_theme_low_nullable.php` |
| LoggerService + interface | `LoggerService.php`, `LoggerServiceInterface.php`, `Contracts/` dir |
| 3 placeholder listeners | `ApplyConfirmedMetadataToPublication`, `NotifyAdminOfMetadataReady`, `NotifyAdminOfScanCompletion`, `Listeners/` dir |
| PartSet (zero refs) | `PartSet.php`, factory, migration |
| Broken artisan command | `ApplyMetadataToPublications.php` (referenced dropped columns) |
| Duplicate cover accessor | `coverImageUrl()` merged into `coverImagePath()` |

### PHASE 1: Model cleanup
- `Publication.php` -- removed `actuality`, `metadata_previous_values`, `id_publishing`, `id_theme_set`, `id_author_set` from fillable
- `File.php` -- removed `file_volume`, `file_number`, `file_page`, `file_size` from fillable; removed composite PK
- `ExtractedMetadata.php` -- removed dead methods, added PHPDoc

### PHASE 2: Structural simplifications
| What changed | Impact |
|-------------|--------|
| **Publishing -> Publisher** | Deleted `Publishing` model/migration/factory. MetadataReviewForm now creates `Publisher` records with i18n. All eager-loads updated. |
| **ThemeSet removed** | Deleted model/migration/factory. Views use `$publication->themes` pivot instead of `$publication->themeSet->theme_set_en`. |
| **AuthorGroup removed** | Deleted model/migration/factory. Views use `$publication->authors->first()` instead of `$publication->authorGroup->author_set`. |
| **FileMetadata fixed** | Added proper `publication_id` FK column. Removed `file_id` string hack and all `explode('-', ...)` / `strtok()` / `SUBSTRING_INDEX` parsing across 8+ files. |
| **File PK fixed** | Added auto-increment `id` column. Kept unique constraint on `(id_publication, file_name)`. Eloquent works properly now. |

### PHASE 2: New traits (OOP patterns)
| Trait | Pattern | Applied to |
|-------|---------|-----------|
| `HasLocalizedName` | Template Method | Publisher, Section, Genre, ContentType, CustomField |
| `AutoLowercasesField` | Observer | Author, Publishing (was), File |

### PHASE 2: Migrations to run
1. `2026_04_07_000001_drop_dead_legacy_columns.php` -- drops `_del_mark`, `actuality`, `metadata_previous_values` from publications; `file_size`, `file_volume`, `file_number`, `file_page` from files
2. `2026_04_07_140000_fix_file_metadata_and_files_primary_keys.php` -- adds `publication_id` FK to file_metadatas, drops `file_id`; adds auto-increment `id` PK to files

### RISK NOTES
- **MetadataReviewDashboard.php** was rewritten heavily (1188 -> ~1070 lines). All the `SUBSTRING_INDEX` raw SQL became proper `whereColumn` joins. Test the dashboard thoroughly.
- **ExtractMetadataFromFile.php** constructor signature changed from `string $fileId` to `int $publicationId`. Any code that dispatches this job with the old string format will break.
- **Publication eager-loads** changed in 6+ Livewire components. Check that list/detail/search pages still load correctly.

### What to do next
```bash
# Fresh database (recommended since beta):
php artisan migrate:fresh

# Or if you want to keep existing data:
php artisan migrate
```