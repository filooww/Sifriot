# Seferium - Library Management System - Brownfield Enhancement Architecture

**Version:** 1.0
**Date:** 2025-10-15
**Status:** Final
**Author:** Winston (Architect Agent)

---

## Table of Contents

1. [Introduction](#1-introduction)
2. [Enhancement Scope and Integration Strategy](#2-enhancement-scope-and-integration-strategy)
3. [Tech Stack](#3-tech-stack)
4. [Data Models and Schema Changes](#4-data-models-and-schema-changes)
5. [Component Architecture](#5-component-architecture)
6. [Source Tree](#6-source-tree)
7. [Infrastructure and Deployment Integration](#7-infrastructure-and-deployment-integration)
8. [Coding Standards](#8-coding-standards)
9. [Testing Strategy](#9-testing-strategy)
10. [Security Integration](#10-security-integration)
11. [Next Steps](#11-next-steps)

---

## 1. Introduction

This document outlines the architectural approach for enhancing the Library Management System with a comprehensive modern library management platform featuring advanced search, bulk upload, metadata extraction, engagement features, and multilingual support. Its primary goal is to serve as the guiding architectural blueprint for AI-driven development of new features while ensuring seamless integration with the existing Laravel 12 foundation.

**Relationship to Existing Architecture:**
This document supplements the existing Laravel project architecture by defining how new components will integrate with current models, migrations, and Livewire components. Where conflicts arise between new and existing patterns, this document provides guidance on maintaining consistency while implementing enhancements.

### 1.1 Existing Project Analysis

#### Current Project State

- **Primary Purpose:** Seferium (Library Management System) for managing books, magazines, articles, and publications with hierarchical organization
- **Current Tech Stack:**
  - Backend: Laravel 12, PHP 8.2+, MySQL 8.0
  - Frontend: Livewire 3, Tailwind CSS 3, Alpine.js 3
  - Infrastructure: Docker (PHP 8.4-Apache, MySQL 8.0, phpMyAdmin)
  - Auth: Laravel Breeze
- **Architecture Style:** Monolithic Laravel application with Livewire component-based UI, following MVC patterns with server-side rendering
- **Deployment Method:** Docker Compose multi-container setup (web, database, admin tools)

#### Available Documentation

- ✅ Comprehensive PRD ([docs/prd.md](docs/prd.md)) - v1.1 with catalog-first architecture, 23 stories defined
- ✅ Migration documentation (MIGRATION_PLAN.md, MIGRATION_SUMMARY.md, MIGRATION_STRATEGY_RU.md)
- ✅ Developer guides (DEVELOPER_GUIDE_RU.md, QUICKSTART.md, LIVEWIRE_GUIDE.md)
- ✅ Legacy system analysis (HTDocs_legacy/README.md)
- ✅ Tech stack configuration (docker-compose.yml, .env.example)
- ✅ Core config ([.bmad-core/core-config.yaml](.bmad-core/core-config.yaml))

#### Identified Constraints

- **Database Schema**: Maintain existing table structure and primary key naming (`id_publication`, `id_author`, etc.)
- **Foreign Key Conventions**: Non-standard FK column naming (e.g., `id_author_set` linking to `author_groups.id_author_group`)
- **Model Relationships**: Some relationships exist but need refinement
- **File Storage**: Large-scale storage (1.1TB) requires local disk storage, path-based references
- **Multi-language**: RTL support needed for Hebrew, locale switching via middleware
- **Queue System**: Database queue driver configured (jobs table exists), needs queue worker process

---

## 2. Enhancement Scope and Integration Strategy

### 2.1 Enhancement Overview

**Enhancement Type:** Technology Stack Upgrade + New Feature Addition (Brownfield Modernization)

**Scope:** Building a comprehensive modern library management system on the existing Laravel 12 foundation, implementing:
- Guest and authenticated browsing modes with role-based access
- Advanced full-text search with MySQL FULLTEXT indexes
- Multi-criteria filtering system
- **Catalog-first file management**: Register existing files on disk (1.1TB) with folder browsing and bulk scanning
- Optional file upload capability alongside registration
- Automatic metadata extraction with configurable rules
- Custom content types with dynamic field definitions
- User engagement features (views, likes, downloads, comments, bookmarks)
- Publication workflow (Published/Hidden/Pending states)
- Full trilingual support (English, Russian, Hebrew with RTL)
- Author profile pages with statistics
- Admin dashboard with analytics
- File integrity monitoring and sync

**Integration Impact:** **Minimal to Moderate** - New features are additive to existing foundation. Existing models/migrations remain intact; new migrations extend schema.

### 2.2 Integration Approach

**Code Integration Strategy:**
- **Extend, Don't Modify**: Existing models have relationships refined and methods added, but core structure preserved
- **Livewire Component Pattern**: Follow existing PublicationList/PublicationForm patterns for all new interactive UI
- **Service Layer**: Encapsulate business logic (MetadataExtractorService, FileStorageService, FolderScanService) separate from controllers
- **Event-Driven**: Use Laravel events for async operations (scan completion notifications, file sync alerts)
- **Migration-Only Schema Changes**: No direct database modifications; all changes via timestamped migrations

**Database Integration:**
- **Additive Migrations Only**: Existing migrations untouched; new tables/columns added via new migration files
- **Standard SoftDeletes**: All tables use Laravel's `SoftDeletes` trait with `deleted_at` timestamp column
- **Relationship Refinement**: Add missing Eloquent relationships to existing models
- **New Tables Required** (19 total):
  - Engagement: `content_views`, `content_likes`, `content_downloads`, `comments`, `comment_upvotes`, `bookmarks`, `bookmark_collections`
  - Content Management: `author_profiles`, `content_types`, `custom_fields`, `custom_field_values`, `content_type_extraction_rules`, `categories`, `category_publication`
  - Catalog-First: `folder_scan_jobs`, `folder_watch_paths`, `file_integrity_checks`, `folder_metadata_rules`, `file_registration_log`
- **Performance Indexes**: FULLTEXT indexes on searchable fields, composite indexes on engagement tables

**API Integration:**
- **Internal API Only**: No external REST API initially; all interactions via Livewire components
- **Livewire Events**: Use `$dispatch` and listeners for inter-component communication
- **Download Endpoints**: Dedicated routes for file downloads with authorization middleware

**UI Integration:**
- **Livewire-First Architecture**: All interactive features as Livewire components (no Vue/React SPA)
- **Blade Layouts**: Use existing AppLayout (authenticated) and GuestLayout (unauthenticated) as base
- **Tailwind CSS Consistency**: Match existing utility class patterns
- **Alpine.js for Micro-Interactions**: Dropdowns, modals, folder tree navigation
- **Virtual Scrolling**: For folder browser with 1000+ files

### 2.3 Compatibility Requirements

**Database Schema Compatibility:**
- ✅ Preserve existing table names and primary key conventions (`id_publication`, `id_author`)
- ✅ Maintain existing foreign key relationships
- ✅ Add SoftDeletes to all tables (new `deleted_at` column)
- ✅ New tables follow Laravel conventions with standard `id` primary keys

**UI/UX Consistency:**
- ✅ Match existing Tailwind design patterns
- ✅ Follow existing form validation and error display patterns
- ✅ Maintain layout structure (navigation, sidebar, content area)

**Performance Impact:**
- ✅ Queue background jobs to avoid blocking (metadata extraction, bulk scanning, file sync)
- ✅ Implement caching for frequently accessed data
- ✅ Use pagination for all list views
- ✅ Eager load relationships to prevent N+1 queries
- ✅ Database queue driver initially; monitor for Redis upgrade need

---

## 3. Tech Stack

### 3.1 Existing Technology Stack

| Category | Current Technology | Version | Usage in Enhancement | Notes |
|----------|-------------------|---------|---------------------|-------|
| **Backend Language** | PHP | 8.4 | Core runtime for all backend logic | Continue using |
| **Backend Framework** | Laravel | 12 | Foundation for all new features | LTS; extend existing |
| **Interactive UI** | Livewire | 3.6.4 | All new interactive components | Follow existing patterns |
| **Frontend CSS** | Tailwind CSS | 3.x | All UI styling | Utility-first |
| **Frontend JS** | Alpine.js | 3.15.0 | Micro-interactions, folder tree | Bundled with Livewire |
| **Templating** | Blade | Laravel 12 | Server-side HTML rendering | Standard Laravel |
| **Authentication** | Laravel Breeze | 2.3 | User auth, registration | Extend for roles |
| **Database** | MySQL | 8.0 | Primary data storage | InnoDB + FULLTEXT indexes |
| **ORM** | Eloquent | Laravel 12 | Database interactions | All models use Eloquent |
| **Queue System** | Laravel Queue (DB) | Laravel 12 | Background jobs | Database driver |
| **File Storage** | Laravel Storage | Laravel 12 | 1.1TB local disk | Path-based references |
| **Asset Bundler** | Vite | 7.0.7 | Frontend asset compilation | Hot reload in dev |
| **Package Manager (PHP)** | Composer | 2.x | PHP dependencies | Existing |
| **Package Manager (JS)** | npm | Latest | JS dependencies | Existing |
| **Testing Framework** | PHPUnit | 11.5.3 | Unit and feature tests | Built-in |
| **Code Quality** | Laravel Pint | 1.24 | PSR-12 formatting | Enforce standards |
| **Development Server** | Apache HTTP | 2.4 | Web server in Docker | PHP 8.4-Apache |
| **Container Platform** | Docker Compose | Latest | Development/production | Multi-container |
| **Database Admin** | phpMyAdmin | Latest | Database UI | Admin tool |

### 3.2 New Technology Additions

| Technology | Version | Purpose | Rationale | Integration Method |
|-----------|---------|---------|-----------|-------------------|
| **smalot/pdfparser** | ^2.0 | Extract text from PDFs | Pure PHP, no dependencies | Composer; MetadataExtractorService |
| **phpoffice/phpword** | ^1.0 | Extract from DOCX files | Official library | Composer; MetadataExtractorService |
| **league/flysystem** | ^3.0 | File browsing, integrity | Included in Laravel | Storage facade |
| **laravel/dusk** | Latest | Browser testing | Critical user flow testing | Composer dev; browser tests |
| **brianium/paratest** | Latest | Parallel test execution | Faster test suite | Composer dev; test command |

**Note:** No Laravel Horizon (queue dashboard) or Spatie Permission needed - simpler alternatives sufficient.

---

## 4. Data Models and Schema Changes

### 4.1 New Data Models

**Engagement Models:**

**ContentView** - Track user views on publications
- `publication_id`, `user_id`, `viewed_at`, `ip_address`
- Implements 24-hour throttling per user
- Relationship: `belongsTo(Publication)`, `belongsTo(User)`

**ContentLike** - User likes on publications
- `publication_id`, `user_id`, `created_at`
- Unique composite index prevents duplicate likes
- Relationship: `belongsTo(Publication)`, `belongsTo(User)`

**ContentDownload** - Download event logging
- `publication_id`, `user_id`, `file_id`, `downloaded_at`, `download_success`
- Relationship: `belongsTo(Publication)`, `belongsTo(User)`, `belongsTo(File)`

**Comment** - User comments with threading
- `commentable_type`, `commentable_id`, `user_id`, `parent_id`, `comment_text`, `is_approved`
- Polymorphic relationship, self-referential for threading
- Plain text only (max 5000 chars)
- Relationship: `morphTo('commentable')`, `belongsTo(User)`, `hasMany(Comment)` (replies)

**CommentUpvote** - Comment voting system
- `comment_id`, `user_id`, `created_at`
- Relationship: `belongsTo(Comment)`, `belongsTo(User)`

**Bookmark** - Private user bookmarks
- `publication_id`, `user_id`, `collection_id`, `notes`
- Relationship: `belongsTo(Publication)`, `belongsTo(User)`, `belongsTo(BookmarkCollection)`

**BookmarkCollection** - Organize bookmarks into folders
- `user_id`, `name`, `description`, `is_default`
- Relationship: `belongsTo(User)`, `hasMany(Bookmark)`

---

**Content Management Models:**

**AuthorProfile** - Extended author information
- `author_id`, `biography_en`, `biography_ru`, `biography_he`, `photo_path`, `social_links`
- One-to-one with Author
- Relationship: `belongsTo(Author)`

**ContentType** - Define content types (Books, Magazines, etc.)
- `name_en`, `name_ru`, `name_he`, `slug`, `icon`, `is_system`
- Relationship: `hasMany(Publication)`, `hasMany(CustomField)`

**CustomField** - Dynamic field definitions
- `content_type_id`, `field_name`, `label_*`, `field_type`, `is_required`, `visibility`, `sort_order`
- Preserves legacy field_config pattern
- Relationship: `belongsTo(ContentType)`, `hasMany(CustomFieldValue)`

**CustomFieldValue** - Polymorphic field value storage
- `custom_field_id`, `fieldable_type`, `fieldable_id`, `value` (JSON)
- Relationship: `morphTo('fieldable')`, `belongsTo(CustomField)`

**ContentTypeExtractionRule** - Metadata extraction patterns
- `content_type_id`, `rule_name`, `target_field`, `extraction_method`, `pattern`, `priority`
- Modernizes legacy algorithm table
- Relationship: `belongsTo(ContentType)`

**Category** - Hierarchical categories
- `parent_id`, `name_en`, `name_ru`, `name_he`, `slug`, `sort_order`
- Self-referential for unlimited depth
- Relationship: `belongsToMany(Publication)`, `belongsTo(Category)` (parent)

---

**Catalog-First Models:**

**FolderScanJob** - Track bulk scanning operations
- `user_id`, `folder_path`, `scan_options`, `status`, `total_files_found`, `files_registered`, `processing_time_seconds`
- Relationship: `belongsTo(User)`, `hasMany(FileRegistrationLog)`

**FolderWatchPath** - Monitored folders for new files
- `folder_path`, `is_active`, `auto_catalog`, `check_frequency`, `last_checked_at`
- Relationship: `hasMany(FileIntegrityCheck)`

**FileIntegrityCheck** - File verification scan logs
- `check_type`, `folder_path`, `total_files_checked`, `files_missing`, `new_files_discovered`, `checked_at`
- Relationship: `belongsTo(FolderWatchPath)` (nullable)

**FolderMetadataRule** - Path pattern → metadata mappings
- `rule_name`, `path_pattern`, `pattern_type`, `priority`, `metadata_mappings` (JSON), `is_enabled`
- Example: `/books/sci-fi/**/*.pdf` → Type="Book", Category="Science Fiction"
- Relationship: `belongsTo(User, 'created_by')`

**FileRegistrationLog** - Audit trail of cataloging
- `publication_id`, `file_path`, `registration_source`, `folder_scan_job_id`, `metadata_auto_extracted`, `status`
- Relationship: `belongsTo(Publication)`, `belongsTo(User)`, `belongsTo(FolderScanJob)`

---

### 4.2 Schema Integration Strategy

**Modified Tables (extend existing):**

**publications** - Add columns:
- `content_type_id`, `status` (enum: published/hidden/pending)
- Denormalized counters: `view_count`, `like_count`, `download_count`, `comment_count`, `bookmark_count`
- File sync: `file_missing`, `file_missing_since`
- Metadata: `original_folder_path`, `word_count`
- `deleted_at` (SoftDeletes)

**users** - Add columns:
- `role` (enum: guest/user/admin)
- `preferred_language` (enum: en/ru/he)
- `deleted_at` (SoftDeletes)

**authors**, **files** - Add:
- `deleted_at` (SoftDeletes)
- `files`: `mime_type`, `file_size_bytes`

**Migration Strategy:**
1. Create new tables (engagement, content management, catalog-first)
2. Extend existing tables (add columns to publications, users)
3. Add FULLTEXT indexes
4. Seed default data (content types, admin user)

---

## 5. Component Architecture

### 5.1 New Components

**Publications/**
- `PublicationDetail` - Detail page with engagement metrics
- `PublicationFilters` - Multi-criteria filtering sidebar

**Search/**
- `GlobalSearch` - Full-text search with autocomplete

**Admin/**
- `FolderBrowser` - Filesystem browser with virtual scrolling (Story 1.6A)
- `FileRegistrationForm` - Register/upload files
- `BulkFolderScanner` - Bulk scan interface with real-time progress (Story 1.7)
- `FileSyncMonitor` - File integrity monitoring (Story 1.6B)
- `FolderMetadataRuleManager` - Folder path rules (Story 1.6C)
- `CustomFieldManager` - Dynamic fields
- `ExtractionRuleManager` - Extraction rules
- `PendingContentQueue` - Pending review queue
- `Dashboard` - Admin analytics dashboard

**Engagement/**
- `LikeButton` - Like/unlike with optimistic UI
- `BookmarkButton` - Bookmark with collection selection
- `CommentSection` - Comments with threading (plain text only, 5000 char max)

**Authors/**
- `AuthorList` - Browse authors
- `AuthorProfile` - Author profile with works

**User/**
- `UserProfile` - Profile and preferences

### 5.2 Component Interaction

All components use Livewire events for communication (`$dispatch`), service classes for business logic, and policies for authorization. Virtual scrolling implemented via `virtual-scroll.js` for folder browser performance.

Real-time notifications via Laravel events: `FolderScanCompleted`, `FileIntegrityIssueDetected` trigger listeners that send notifications to admins.

---

## 6. Source Tree

### 6.1 File Organization

```
app/
├── Models/                           # 18 new + 12 existing models
├── Livewire/                         # Feature-organized components
│   ├── Publications/
│   ├── Search/
│   ├── Admin/                        # Folder browser, scan, sync, rules
│   ├── Engagement/
│   ├── Authors/
│   └── User/
├── Services/                         # NEW: Business logic
│   ├── MetadataExtractorService.php
│   ├── FileStorageService.php
│   ├── FolderScanService.php
│   ├── FileSyncService.php
│   └── FolderRuleService.php
├── Http/
│   ├── Controllers/
│   │   ├── DownloadController.php   # File downloads
│   │   └── LanguageSwitcherController.php
│   ├── Middleware/
│   │   ├── EnsureUserRole.php       # Role-based access
│   │   └── DownloadAuthorization.php
│   └── Requests/                     # Form validation
├── Policies/                         # Authorization
├── Events/                           # Scan completion, file alerts
├── Listeners/                        # Notification sending
├── Jobs/                             # Queue jobs
├── Notifications/                    # Notification classes
└── Logging/                          # NEW: ColoredLineFormatter

database/
├── migrations/                       # 23+ new migrations
├── factories/                        # Test data factories
└── seeders/                          # Initial data

resources/
├── views/
│   ├── livewire/                     # Component views (kebab-case)
│   ├── components/                   # Reusable Blade components
│   └── layouts/
├── lang/                             # en.json, ru.json, he.json
├── css/
└── js/
    └── virtual-scroll.js             # NEW: Virtual scrolling library

storage/
├── app/
│   └── content/                      # 1.1TB volume mount
│       ├── books/
│       ├── magazines/
│       ├── articles/
│       └── other/
└── logs/
    ├── folder-scan.log               # Colored logs
    └── file-sync.log                 # Colored logs

config/
└── library.php                       # NEW: App-specific config

tests/
├── Feature/                          # Integration + regression
└── Unit/                             # Service + model logic
```

### 6.2 Integration Guidelines

**File Naming:** Models (PascalCase), Livewire PHP (PascalCase), Livewire views (kebab-case), migrations (timestamp + snake_case)

**Folder Organization:** Feature-based for Livewire, type-based for services/models

**Import/Export:** Services via DI, Livewire via events, relationships via Eloquent

---

## 7. Infrastructure and Deployment Integration

### 7.1 Enhanced docker-compose.yml

**New Containers:**
- `literature_queue` - Queue worker (`php artisan queue:work --tries=3 --timeout=600`)
- `literature_scheduler` - Cron scheduler (runs `schedule:run` every 60 seconds)

**Volume Mounts:**
- `/mnt/library-storage:/var/www/html/storage/app/content` - Local disk (1.1TB)

**Healthcheck Directives:**
```yaml
healthcheck:
  test: ["CMD", "php", "artisan", "health:check"]
  interval: 30s
  timeout: 10s
  retries: 3
```

### 7.2 Deployment Strategy

**Deployment Script (deploy.sh):**
```bash
# Stop containers
docker compose down

# Database backup (automated)
docker compose exec literature_db mysqldump -u root -p$DB_PASSWORD literature > backup_$(date +%Y%m%d_%H%M%S).sql

# Update dependencies
docker compose run --rm literature_web composer install --optimize-autoloader --no-dev
docker compose run --rm literature_web npm ci && npm run build

# Run migrations
docker compose run --rm literature_web php artisan migrate --force

# Clear caches
docker compose run --rm literature_web php artisan optimize:clear
docker compose run --rm literature_web php artisan config:cache

# Restart
docker compose up -d
```

### 7.3 Monitoring

**Health Check Endpoint (`/health`):**
- Checks database connection, storage accessibility, queue worker heartbeat
- Returns 200 (healthy) or 503 (unhealthy)

**Colored Logs:**
- Custom `ColoredLineFormatter` with ANSI codes
- `folder-scan.log` - Green (info), Yellow (warning), Red (error)
- `file-sync.log` - Cyan (info), Yellow (warning), Red (error)

**Queue Monitoring:**
- Queue worker heartbeat via cache every 60s
- Admin dashboard shows failed jobs count

**Scheduled Tasks:**
- File integrity check (configurable: daily, weekly, hourly)
- Cleanup old logs (keep 90 days)
- Engagement metrics reconciliation (daily)
- Database backup (weekly)

---

## 8. Coding Standards

### 8.1 Existing Standards

- **PSR-12** via Laravel Pint
- **Strict typing** in service classes (`declare(strict_types=1)`)
- **Type hints** on all methods
- **PHPUnit** for testing
- **DocBlocks** for complex methods

### 8.2 Enhancement-Specific Patterns

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

### 8.3 Critical Integration Rules

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

### 8.4 Pre-commit Hooks (Enforced)

```bash
#!/bin/sh
./vendor/bin/pint
php artisan test --testsuite=Unit --stop-on-failure
```

---

## 9. Testing Strategy

### 9.1 Test Framework

- **PHPUnit 11.5.3** with Laravel helpers
- **Laravel Dusk** for browser testing (critical flows)
- **ParaTest** for parallel execution
- **Coverage target**: 70%+ for models and services

### 9.2 Test Categories

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

### 9.3 Test Infrastructure

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

## 10. Security Integration

### 10.1 Existing Security

- Laravel Breeze authentication (bcrypt passwords)
- CSRF protection on all forms
- XSS prevention (Blade escaping)
- SQL injection prevention (Eloquent ORM)

### 10.2 Enhancement Security

**Role-Based Access Control:**
- Guest (unauthenticated): Browse limited, search
- User (authenticated): Full access, download, engagement
- Admin: All + upload, edit, delete, moderation

**Implementation:**
- Middleware: `EnsureUserRole`
- Policies: `PublicationPolicy`, `CommentPolicy`
- User model: `role` enum column

**File Download Authorization:**
- Middleware: `DownloadAuthorization`
- Policy check before serving file
- Download logging and rate limiting (60/minute)

**File Path Validation:**
```php
// FileStorageService validates all paths
public function validatePath(string $filePath): string
{
    $realPath = realpath($filePath);

    if (!str_starts_with($realPath, $this->basePath)) {
        throw new InvalidFilePathException("Path traversal attempt");
    }

    return $realPath;
}
```

**Rate Limiting:**
- Authentication: 5 attempts/minute
- Downloads: 60/minute
- Comments: 10/hour

**Input Validation:**
- Form Requests for complex validation
- MIME type verification (not just extension)
- File size limits (500MB)

**Security Testing:**
- Path traversal tests
- Authorization bypass attempts
- XSS/SQL injection tests

---

## 11. Next Steps

### 11.1 Implementation Roadmap

**Phase 1: Foundation (Weeks 1-3)** - Stories 1.1-1.3
- Model relationships + SoftDeletes
- Multi-language + RTL
- Role-based access control

**Phase 2: Core Features (Weeks 4-5)** - Stories 1.4-1.5
- Full-text search with FULLTEXT indexes
- Multi-criteria filtering

**Phase 3: Catalog-First File Management (Weeks 6-11)** - Stories 1.6-1.8
- Folder browser with virtual scrolling
- File registration and upload
- Folder metadata rules
- Bulk scanning with queue jobs
- File sync monitoring
- Metadata extraction

**Phase 4: Advanced Features (Weeks 12-13)** - Stories 1.9-1.10
- Custom content types and fields
- Publication detail page

**Phase 5: Engagement (Weeks 14-17)** - Stories 1.11-1.16
- View tracking, likes, downloads
- Comment system (plain text, 5000 char max)
- Bookmarks with collections
- Publication workflow

**Phase 6: Polish (Weeks 18-20)** - Stories 1.17-1.20
- Author profiles
- Admin dashboard
- Extraction rules manager
- User profile

### 11.2 Developer Quick Start

```bash
# Setup
docker compose up -d
docker compose exec literature_web composer install
docker compose exec literature_web npm install
docker compose exec literature_web php artisan migrate
docker compose exec literature_web npm run dev

# Pre-commit hooks
cat > .git/hooks/pre-commit << 'EOF'
#!/bin/sh
./vendor/bin/pint
php artisan test --testsuite=Unit --stop-on-failure
EOF
chmod +x .git/hooks/pre-commit

# Verify
docker compose exec literature_web php artisan test
```

### 11.3 Key Technical Reminders

- **Always specify foreign/local keys** explicitly in relationships
- **Use FileStorageService** for all filesystem operations
- **Queue jobs** with error handling (`onFailure`)
- **Log to specific channels** (`folder_scan`, `file_sync`)
- **Virtual scrolling** for large directories
- **Test in all three languages** (EN, RU, HE with RTL)
- **Queue worker must be running** for background jobs

### 11.4 Useful Commands

```bash
# Testing
php artisan test --coverage --min=70
./vendor/bin/paratest

# Queue
php artisan queue:work
php artisan queue:monitor
php artisan queue:retry all

# Logs (colored)
tail -f storage/logs/folder-scan.log
tail -f storage/logs/file-sync.log

# Maintenance
php artisan migrate:fresh --seed
php artisan optimize:clear
```

---

**End of Architecture Document**

**Next Action:** Begin Story 1.1 (Content Model Refinement and Relationships) - See [docs/prd.md](docs/prd.md) for acceptance criteria.
