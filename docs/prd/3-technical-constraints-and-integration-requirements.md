# 3. Technical Constraints and Integration Requirements

## 3.1 Existing Technology Stack

**Languages:**
- PHP 8.4

**Frameworks:**
- Laravel 12
- Livewire 3 (for reactive UI components)
- Laravel Breeze (authentication scaffolding)

**Frontend:**
- Tailwind CSS 3.x (utility-first CSS)
- Alpine.js (bundled with Livewire for lightweight JS interactions)
- Blade templating engine

**Database:**
- MySQL 8.0 (primary database)
- Supports full-text search indexes
- InnoDB engine with foreign key constraints

**Infrastructure:**
- Docker containers (development and production)
  - `literature_web`: PHP 8.4 + Apache
  - `literature_db`: MySQL 8.0
  - `literature_phpmyadmin`: Database admin tool
- Local file system storage (1.1TB capacity on dedicated volume)
- Apache HTTP Server 2.4

**Development Tools:**
- Composer (PHP dependency management)
- npm/Vite (frontend asset compilation)
- PHPUnit (testing framework, included in Laravel)

**External Dependencies:**
- Laravel's built-in packages (Eloquent ORM, Queue system, Cache, Session, Validation)
- Livewire 3 for component-based interactivity
- Potential additions: PDF parsing library (Smalot/PdfParser), DOCX parsing (PhpOffice/PhpWord)

## 3.2 Integration Approach

**Database Integration Strategy:**
- **Extend existing schema**: All new features add migrations without modifying existing ones
- **Model refinement**: Existing models (Publication, Author, AuthorGroup, etc.) will have relationships and methods added/refined
- **New tables required**:
  - `content_views` (track views per user per publication)
  - `content_likes` (user likes on publications)
  - `content_downloads` (download history)
  - `comments` (user comments on publications)
  - `comment_likes` (upvotes on comments)
  - `bookmarks` (user bookmarks, private)
  - `author_profiles` (optional detailed author information)
  - `content_type_extraction_rules` (configurable metadata extraction patterns)
  - `custom_fields` (dynamic field definitions per content type)
  - `custom_field_values` (polymorphic table for dynamic field data)
  - `folder_scan_jobs` (track bulk scanning operations with status and results)
  - `folder_watch_paths` (monitored folders for automatic file discovery)
  - `file_integrity_checks` (log of file verification scans and results)
  - `folder_metadata_rules` (path pattern rules → metadata mappings)
  - `file_registration_log` (audit trail of file cataloging actions)
- **Indexes**: Add indexes on frequently queried columns (publication_id, user_id, created_at on engagement tables; status on publications; category_id for filtering)
- **Full-text search**: Enable MySQL FULLTEXT indexes on `publications.title`, `publications.description`, `authors.name`

**API Integration Strategy:**
- **Internal API only**: No external API required initially
- **Livewire component communication**: Use Livewire events (`$dispatch`, `$on`) for inter-component messaging
- **RESTful principles**: Follow Laravel resource routing conventions for admin CRUD operations
- **Future consideration**: API endpoints for mobile app or integrations (not in initial scope)

**Frontend Integration Strategy:**
- **Livewire-first approach**: All interactive features built as Livewire components
- **No separate SPA**: Leverage Livewire's server-rendered reactive UI (no Vue/React)
- **Component structure**:
  ```
  app/Livewire/
  ├── Publications/
  │   ├── PublicationList.php ✓ (exists)
  │   ├── PublicationForm.php ✓ (exists)
  │   ├── PublicationDetail.php (new)
  │   └── PublicationFilters.php (new)
  ├── Authors/
  │   ├── AuthorList.php (new)
  │   ├── AuthorProfile.php (new)
  ├── Engagement/
  │   ├── LikeButton.php (new)
  │   ├── BookmarkButton.php (new)
  │   ├── CommentSection.php (new)
  ├── Admin/
  │   ├── CustomFieldManager.php (new)
  │   ├── ExtractionRuleManager.php (new)
  │   ├── PendingContentQueue.php (new)
  └── Search/
      └── GlobalSearch.php (new)
  ```
- **Tailwind CSS consistency**: All components use utility classes matching existing design
- **Layouts**: `AppLayout` for authenticated users, `GuestLayout` for unauthenticated

**Testing Integration Strategy:**
- **Feature tests**: Test Livewire components using `Livewire::test()` helper
- **Unit tests**: Test models, relationships, and service classes
- **Browser tests** (optional): Laravel Dusk for critical user flows
- **Test database**: Separate `literature_test` database (configured in phpunit.xml)
- **Coverage target**: Minimum 70% code coverage for models and services

## 3.3 Code Organization and Standards

**File Structure Approach:**
```
app/
├── Models/              # Eloquent models (existing + new)
├── Livewire/           # Livewire components (organized by feature)
├── Services/           # Business logic services
│   ├── MetadataExtractor.php (new)
│   ├── FileStorageService.php (new)
│   └── EngagementService.php (new)
├── Http/
│   ├── Controllers/    # Traditional controllers (minimal, prefer Livewire)
│   ├── Middleware/     # SetLocale.php exists, add DownloadAuthorization.php
│   └── Requests/       # Form request validation classes
├── Policies/           # Authorization policies (PublicationPolicy, CommentPolicy)
└── Events/             # Event classes for notifications

database/
├── migrations/         # All schema changes (chronological)
├── factories/          # Model factories for testing
└── seeders/           # Data seeders for development

resources/
├── views/
│   ├── livewire/      # Livewire component views
│   ├── layouts/       # AppLayout, GuestLayout
│   └── components/    # Blade components
├── lang/
│   ├── en.json        # English translations
│   ├── ru.json        # Russian translations
│   └── he.json        # Hebrew translations
└── css/
    └── app.css        # Tailwind imports

storage/
└── app/
    └── content/       # Local file storage root (1.1TB volume mount)
        ├── books/
        ├── magazines/
        ├── articles/
        └── other/
```

**Naming Conventions:**
- **Models**: Singular, PascalCase (`Publication`, `Author`, `Comment`)
- **Controllers**: PascalCase with `Controller` suffix (`PublicationController`)
- **Livewire components**: Namespace/Feature format (`Publications/PublicationList`)
- **Routes**: Plural resource names (`/publications`, `/authors`)
- **Database tables**: Plural, snake_case (`publications`, `content_likes`)
- **Relationships**: Singular for `belongsTo`, plural for `hasMany/belongsToMany`
- **Methods**: camelCase for models/controllers, snake_case for database columns

**Coding Standards:**
- PSR-12 coding style (enforced via Laravel Pint)
- Type hints on all method parameters and return types
- Strict types declaration (`declare(strict_types=1)`) in service classes
- DocBlocks for complex methods explaining parameters and business logic
- Use Laravel's built-in helpers (`collect()`, `optional()`, etc.)
- Dependency injection via constructor for services

**Documentation Standards:**
- README.md in each major directory explaining purpose
- Inline comments for complex business logic only (code should be self-documenting)
- API-style documentation for service classes (parameters, return values, exceptions)
- Database schema documented in migration comments
- Livewire components include docblock describing purpose and public properties

## 3.4 Deployment and Operations

**Build Process Integration:**
- **Development**: `docker compose up` starts all services
- **Assets**: `npm run dev` for development, `npm run build` for production (Vite)
- **Dependencies**: `composer install` for PHP packages
- **Migrations**: `php artisan migrate` for database schema updates
- **Translations**: `php artisan lang:publish` for publishing language files

**Deployment Strategy:**
- **Environment config**: `.env` file for environment-specific settings
- **Storage configuration**:
  ```
  FILESYSTEM_DISK=local
  STORAGE_PATH=/mnt/library-storage  # 1.1TB volume mount point
  ```
- **Database migrations**: Run `php artisan migrate` during deployment
- **Cache clearing**: `php artisan optimize:clear` before deployment
- **Queue workers**: Database queue driver (no Redis required initially)
  - Development: `php artisan queue:work` in Docker container terminal
  - Production: Docker container with `command: php artisan queue:work` or supervisor inside container
- **Scheduled tasks**: `php artisan schedule:work` for cron jobs (cleanup, statistics aggregation)

**Queue Processing:**
- **Queue driver**: Database (stores jobs in `jobs` table)
- **What gets queued**:
  - `ProcessMetadataExtraction` - Extracts metadata from uploaded files
  - `ProcessBulkUploadFile` - Processes individual files during bulk upload
  - `UpdateEngagementCounters` - Updates view/like/download counts (optional, can be synchronous)
- **Failed jobs**: Store in `failed_jobs` table; admin dashboard shows failed items for retry
- **Worker management**: Supervisor or Docker restart policy keeps worker alive

**Monitoring and Logging:**
- **Laravel Log**: All errors logged to `storage/logs/laravel.log`
- **Log channels**: Configure separate channels for security events, upload failures, metadata extraction errors
- **Error tracking**: Log errors with context (user ID, publication ID, stack trace)
- **Performance monitoring**: Consider Laravel Telescope (dev only) or custom metrics
- **Storage monitoring**: Track disk usage via system monitoring tools (not application-level)

**Configuration Management:**
- **Environment variables**: All sensitive config in `.env` (database credentials, storage paths)
- **Config files**: `config/filesystems.php` for storage, `config/app.php` for locales
- **Custom config**: Create `config/library.php` for app-specific settings:
  ```php
  return [
      'storage' => [
          'max_file_size' => env('MAX_FILE_SIZE', 524288), // 512MB in KB
          'allowed_types' => ['pdf', 'epub', 'txt', 'docx'],
      ],
      'engagement' => [
          'view_throttle_hours' => 24,
          'comment_rate_limit' => 10, // per hour
      ],
      'extraction' => [
          'enabled' => env('METADATA_EXTRACTION_ENABLED', true),
      ],
  ];
  ```
- **Cache config**: `php artisan config:cache` in production

## 3.5 Risk Assessment and Mitigation

**Technical Risks:**

| Risk | Likelihood | Impact | Mitigation Strategy |
|------|-----------|--------|-------------------|
| **1.1TB storage volume fills up** | Medium | High | Implement storage quota monitoring; alert at 80% capacity; document cleanup procedures for soft-deleted files |
| **Slow metadata extraction on large files** | High | Medium | Process extraction in queue jobs (async); add timeout limits; provide manual override option |
| **Database performance degradation with 100k+ items** | Medium | High | Implement proper indexing; use query optimization (eager loading); cache frequently accessed data (popular content) |
| **Concurrent upload conflicts** | Low | Medium | Use database transactions; implement file locking during upload; validate uniqueness before saving |
| **Model relationship complexity** | High | Medium | Thoroughly test relationships; document entity-relationship diagram; use factories for testing all scenarios |

**Integration Risks:**

| Risk | Likelihood | Impact | Mitigation Strategy |
|------|-----------|--------|-------------------|
| **Livewire component performance with large lists** | Medium | Medium | Implement pagination on all lists; use lazy loading; defer non-critical data loading |
| **Multi-language RTL (Hebrew) layout issues** | Medium | Low | Test all components with Hebrew text; use Tailwind RTL modifiers; validate form layouts |
| **File download performance under load** | Medium | Medium | Implement download rate limiting; consider X-Sendfile for efficient file serving via web server; monitor bandwidth |
| **Comment spam** | Low | Low | Implement rate limiting (10 comments/hour); admin moderation queue; soft delete abusive comments |
| **Search performance on large datasets** | High | Medium | Use MySQL FULLTEXT indexes; implement query result caching; consider search pagination limits |

**Deployment Risks:**

| Risk | Likelihood | Impact | Mitigation Strategy |
|------|-----------|--------|-------------------|
| **Migration failures** | Low | Critical | Test all migrations on copy of production DB; keep migration rollback methods updated; backup before deployment |
| **Storage path misconfiguration** | Medium | Critical | Validate storage paths during deployment; document mount point requirements; implement health check endpoint |
| **Queue worker not running** | Medium | Medium | Monitor queue with supervisor; implement queue failure alerting; document worker setup procedures |
| **Cache stale data after deployment** | High | Low | Always run `php artisan optimize:clear` during deployment; document cache strategy |
| **Translation file sync issues** | Low | Low | Version control all language files; automated tests verify all keys exist in all languages |

**Mitigation Strategies Summary:**

1. **Storage Management**:
   - Configure storage path via `.env` to separate volume
   - Implement admin dashboard showing storage usage
   - Document backup strategy for 1.1TB volume (external to application)

2. **Performance**:
   - Use Redis for caching if needed in future (database cache sufficient initially)
   - Queue background jobs (metadata extraction, engagement counter updates)
   - Implement database query logging in development to identify N+1 problems

3. **Data Integrity**:
   - Use database transactions for multi-step operations
   - Implement comprehensive validation at model and request level
   - Soft delete everywhere to enable recovery

4. **Monitoring**:
   - Log all critical operations (uploads, downloads, admin actions)
   - Implement health check endpoint (`/health`) checking DB and storage
   - Monitor queue length and failed jobs

5. **Testing**:
   - Comprehensive feature tests for all Livewire components
   - Test file uploads with various sizes and formats
   - Test multi-language features with all three languages
   - Load testing for search and filtering with 100k+ records

---
