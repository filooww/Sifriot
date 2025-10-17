# 9. Testing Strategy

## 9.1 Test Framework

- **PHPUnit 11.5.3** with Laravel helpers
- **Laravel Dusk** for browser testing (critical flows)
- **ParaTest** for parallel execution
- **Coverage target**: 70%+ for models and services

## 9.2 Test Categories

**Unit Tests (40%):**
- Services: MetadataExtractorService, FolderRuleService, FileSyncService
- Models: Relationship verification
- Pattern matching: Folder metadata rules

**Feature Tests (60%):**
- Livewire components: File registration, bulk scan, folder browser
- Workflows: Full scan operation, metadata extraction
- Integration: Search + filter, engagement features

**Browser Tests (Dusk):**
- Multi-language switching (EN, RU, HE with RTL)
- Folder browser with virtual scrolling
- File upload and registration flow

**Regression Tests:**
- Ensure existing PublicationList/PublicationForm still work
- Authentication flows unchanged
- All existing features functional

## 9.3 Test Infrastructure

**Test Database:** `literature_test` (configured in phpunit.xml)

**Fixtures:**
- Sample PDF, DOCX, EPUB in `tests/fixtures/`
- Folder structure samples for scan tests

**Factories:**
- All models have factories for consistent test data
- State modifiers (e.g., `Publication::factory()->pending()`)

**Commands:**
```bash
# Run all tests
php artisan test

# Run with coverage (on-demand)
php artisan test --coverage --min=70

# Parallel execution
./vendor/bin/paratest

# Browser tests
php artisan dusk
```

---
