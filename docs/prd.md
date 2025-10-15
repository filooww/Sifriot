# Library Management System - Brownfield Enhancement PRD

**Version:** 1.1
**Date:** 2025-10-15
**Status:** Approved
**Author:** PM Agent (John), Updated by PO Agent (Sarah)

---

## Table of Contents

1. [Intro Project Analysis and Context](#1-intro-project-analysis-and-context)
2. [Requirements](#2-requirements)
3. [Technical Constraints and Integration Requirements](#3-technical-constraints-and-integration-requirements)
4. [Epic and Story Structure](#4-epic-and-story-structure)
5. [Epic 1: Modern Library Management System](#5-epic-1-modern-library-management-system)

---

## 1. Intro Project Analysis and Context

### 1.1 Analysis Source

**Analysis Type:** IDE-based fresh analysis with existing Laravel foundation

### 1.2 Current Project State

**Project:** Library Management System
**Current Status:** Fresh Laravel 12 setup with foundational models and migrations

**What exists:**
- ✅ Laravel 12 + Livewire 3 + Tailwind CSS configured
- ✅ Docker environment (PHP 8.4, MySQL 8.0, phpMyAdmin)
- ✅ Core models created: `Publication`, `Author`, `AuthorGroup`, `Publishing`, `IssueType`, `Magazine`, `Part`, `PartSet`, `ThemeSet`, `Theme`, `File`
- ✅ Database migrations for core schema
- ✅ Laravel Breeze authentication installed
- ✅ Basic Livewire components: `PublicationList`, `PublicationForm`

**What the system will do:**
Replace a legacy PHP bibliographic database system with a modern library management platform that handles books, magazines, articles, and custom content types with advanced search, filtering, metadata extraction, and multi-language support.

### 1.3 Available Documentation Analysis

**Document-project status:** Not run - using manual analysis

**Available Documentation:**
- ✅ Tech Stack Documentation (docker-compose.yml, .env.example)
- ✅ Migration documentation (MIGRATION_PLAN.md, MIGRATION_SUMMARY.md, MIGRATION_STRATEGY_RU.md)
- ✅ Developer guides (DEVELOPER_GUIDE_RU.md, QUICKSTART.md, LIVEWIRE_GUIDE.md)
- ✅ Legacy system analysis (HTDocs_legacy/README.md)
- ⚠️ API Documentation - To be created
- ⚠️ UX/UI Guidelines - To be created
- ⚠️ Coding Standards - Partial (coding-standards.md referenced in config)
- ⚠️ Technical Debt Documentation - Legacy system documented, new system TBD

### 1.4 Enhancement Scope Definition

**Enhancement Type:**
- ✅ **New Feature Addition** (building complete system)
- ✅ **Technology Stack Upgrade** (PHP 5.5 → PHP 8.4, procedural → Laravel)

**Enhancement Description:**
Building a comprehensive library management system on the fresh Laravel foundation. This includes implementing all core features: guest/authenticated browsing, advanced search and filtering across multiple content types (books, magazines, articles), bulk upload with local file management, automatic metadata extraction from documents, admin-defined custom fields, and full trilingual support (English, Russian, Hebrew).

**Impact Assessment:**
- ✅ **Minimal Impact** (isolated additions to existing Laravel foundation)
- The existing models and migrations provide the base schema; we're building features on top without breaking existing structure

### 1.5 Goals and Background Context

**Goals:**
- Provide a modern, user-friendly library management system replacing legacy PHP application
- Enable seamless browsing without registration with full features after authentication
- Implement powerful search and filtering across all content dimensions (title, author, category, date, genre, text size)
- Support bulk content uploads with automated metadata extraction
- Allow administrators to define custom fields for flexible content organization
- Deliver full trilingual experience (English, Russian, Hebrew) throughout the application
- Maintain local file storage with path-based references for scalability
- Provide intuitive content management for books, magazines, articles, and extensible content types

**Background Context:**

This project modernizes a sophisticated legacy bibliographic database system that has served users well since 2010-2015 but has accumulated technical debt. The legacy system features 186 PHP files managing complex data relationships, dynamic field configurations, text parsing algorithms, and hierarchical structures across 4 MySQL databases.

A fresh Laravel 12 foundation has been established with core models and migrations representing the modernized data schema. The system will preserve powerful legacy features (dynamic fields, metadata extraction, hierarchical organization) while leveraging Laravel's security, maintainability, and modern development patterns. No legacy compatibility is required—we're building optimally for the future while learning from past success.

### 1.6 Change Log

| Change | Date | Version | Description | Author |
|--------|------|---------|-------------|--------|
| Initial PRD | 2025-10-14 | 1.0 | Created comprehensive brownfield PRD for library management system | PM Agent (John) |
| Catalog-First Architecture | 2025-10-15 | 1.1 | Updated to catalog-first architecture: Changed FR6/FR7 from upload-centric to file registration/scanning; Added FR24A (File Sync), FR24B (Folder Metadata); Modified Stories 1.6-1.8; Added Stories 1.6A-1.6C; Added 5 new database tables | PO Agent (Sarah) |

---

## 2. Requirements

### 2.1 Functional Requirements

**FR1: Guest Browsing Mode**
- The system shall allow unauthenticated users to browse content with limited access (titles, basic metadata visible; full content access requires authentication)

**FR2: User Registration and Authentication**
- The system shall provide user registration with email verification
- The system shall authenticate users via email/password with "remember me" functionality
- The system shall support password reset functionality

**FR3: Content Type Management**
- The system shall support four primary content types: Books, Magazines, Articles, and Other
- The system shall allow administrators to define additional custom content types
- Each content type shall support type-specific metadata fields
- Each content type shall have configurable metadata extraction rules/patterns

**FR4: Search Functionality**
- The system shall provide full-text search across content titles
- The system shall support search across author names, descriptions, and metadata fields
- The system shall provide search without requiring authentication (with limited result details)
- The system shall return search results with relevance ranking

**FR5: Advanced Filtering**
- The system shall filter content by name (title)
- The system shall filter content by categories/genres
- The system shall filter content by alphabetical order (A-Z, Z-A)
- The system shall filter content by text size (word count ranges)
- The system shall filter content by author
- The system shall filter content by publication date (year, date ranges)
- The system shall filter content by genre/subject matter
- The system shall filter content by publication status (Published, Hidden, Pending)
- The system shall filter content by popularity metrics (most viewed, most liked, most downloaded)
- The system shall support combining multiple filters simultaneously

**FR6: Content Registration and Optional Upload**
- The system shall allow administrators to register existing files on disk as publications
- The system shall provide folder browser interface to navigate server filesystem
- The system shall display unregistered files in browsed folders
- The system shall allow administrators to upload new individual content files
- The system shall support multiple readable formats (PDF, EPUB, TXT, DOCX, etc.)
- The system shall store only file paths and names (local storage, not database storage)
- The system shall validate file types and formats before registration or upload
- The system shall generate unique identifiers for registered/uploaded content
- The system shall handle large files efficiently (files up to 500MB)
- The system shall prevent duplicate file registrations (same path)

**FR7: Bulk Folder Scanning and Cataloging**
- The system shall allow administrators to scan server folders containing existing files
- The system shall recursively process subfolders during bulk cataloging operations
- The system shall identify and list unregistered files in scanned folders
- The system shall extract and preserve original folder structure as organizational metadata
- The system shall provide scanning progress indication for bulk operations
- The system shall generate summary reports after bulk cataloging completion (files found, registered, skipped, errors)
- The system shall handle bulk operations on large datasets (1.1TB+ total storage)
- The system shall detect and flag duplicate file paths during scanning
- The system shall allow administrators to filter scan results (by format, size, registration status)

**FR8: Content Metadata Management**
- The system shall allow administrators to manually edit content name/title
- The system shall allow editing of categories/genres
- The system shall allow editing of descriptions
- The system shall allow editing of author information
- The system shall support cover image upload and editing
- The system shall store physical storage location (folder path) as metadata
- The system shall display file discovery source (manual upload, bulk scan, folder monitor)
- The system shall track original folder structure path for organizational reference
- The system shall support editing of all custom-defined metadata fields
- The system shall allow administrators to set publication status (Published/Hidden/Pending)

**FR9: Automatic Metadata Extraction**
- The system shall automatically extract text content from registered or uploaded files
- The system shall parse extracted text to identify potential author names
- The system shall parse extracted text to identify potential titles
- The system shall extract publication year from document metadata or content
- The system shall extract other metadata (publisher, ISBN, etc.) when available
- The system shall present extracted metadata to administrators for confirmation and editing before saving
- The system shall support configurable extraction rules per content type
- The system shall allow administrators to define custom regex patterns for extraction

**FR10: Custom Field Definition**
- The system shall allow administrators to define custom metadata fields for any content type
- The system shall support multiple field types (text, number, date, dropdown, multiselect, boolean)
- The system shall allow administrators to mark fields as required or optional
- The system shall allow administrators to set field visibility (public, authenticated only, admin only)
- The system shall allow administrators to reorder fields for display purposes
- The system shall validate data according to field type definitions

**FR11: Multi-Language Support**
- The system shall support three languages: English, Russian, and Hebrew
- The system shall allow users to switch interface language via dropdown/selector
- The system shall persist language preference for authenticated users
- The system shall default to browser language for guest users (with fallback to English)
- All system UI elements shall be translatable
- Content metadata fields shall support multilingual values (title, description in multiple languages)

**FR12: Content Deletion**
- The system shall allow administrators to delete content items
- The system shall use soft-delete mechanism (mark as deleted without physical removal)
- The system shall hide soft-deleted content from regular users
- The system shall provide administrators with option to view deleted content
- The system shall allow permanent deletion or restoration of soft-deleted content

**FR13: Content Editing**
- The system shall provide inline editing interface for content metadata
- The system shall track modification history (who edited, when)
- The system shall validate all edits before saving
- The system shall provide "cancel" functionality to discard unsaved changes

**FR14: Author Management**
- The system shall maintain a separate authors catalog
- The system shall support multiple authors per content item
- The system shall allow grouping authors (e.g., "Author Group" as collective entity)
- The system shall prevent duplicate author entries
- The system shall allow merging duplicate authors
- The system shall optionally support detailed author profiles with biography, photo, and social links
- The system shall provide author pages displaying all content by that author
- The system shall track statistics per author (total works, total views, followers)

**FR15: Category/Genre Management**
- The system shall maintain hierarchical category structure
- The system shall allow administrators to create, edit, and delete categories
- The system shall support multiple categories per content item
- The system shall support nested subcategories (unlimited depth)

**FR16: Content Display**
- The system shall provide list view of content with pagination
- The system shall provide detailed view showing all metadata and file information
- The system shall provide grid/card view as alternative layout
- Authenticated users shall see "download" or "access" links for full content
- The system shall display cover images when available
- The system shall display engagement metrics on content pages (views, likes, downloads, comments, bookmarks)

**FR17: User Roles and Permissions**
- The system shall support minimum three user roles: Guest (unauthenticated), Registered User, Administrator
- Guest users: browse with limited details, search
- Registered users: full content access, download capabilities, engagement features (like, comment, bookmark)
- Administrators: all user capabilities plus upload, edit, delete, custom field management, content moderation
- The system shall allow role assignment during user creation or profile editing

**FR18: Dashboard and Statistics**
- The system shall provide authenticated users with a dashboard showing recent additions
- The system shall display statistics: total items, items by category, recent uploads
- Administrators shall see additional statistics: user count, storage usage, pending reviews
- The system shall display trending content based on recent engagement metrics
- The system shall provide analytics dashboard for administrators (views over time, popular categories, user growth)

**FR19: Content Engagement - Views Tracking**
- The system shall track view count for each content item
- The system shall increment view count when authenticated users access full content
- The system shall prevent duplicate view counting from same user within 24 hours
- The system shall display view count on content pages

**FR20: Content Engagement - Likes**
- The system shall allow authenticated users to like content items
- The system shall allow users to unlike previously liked content
- The system shall display total like count on content pages
- The system shall indicate to users which content they have liked
- The system shall maintain list of liked content in user profile

**FR21: Content Engagement - Downloads Tracking**
- The system shall track download count for each content item
- The system shall increment download count when authenticated users download files
- The system shall log download history per user
- The system shall display download count on content pages

**FR22: Content Engagement - Comments**
- The system shall allow authenticated users to comment on content items
- The system shall support threaded/nested comments (replies to comments)
- The system shall allow users to edit or delete their own comments
- The system shall allow administrators to moderate (edit/delete) any comments
- The system shall display comments chronologically or by popularity
- The system shall support comment upvoting by authenticated users
- The system shall implement rate limiting: 10 comments per hour per user
- Admin moderation queue sufficient for spam control (no automated filtering required initially)

**FR23: Content Engagement - Bookmarks**
- The system shall allow authenticated users to bookmark content for later reading
- The system shall maintain a "My Bookmarks" collection in user profile
- The system shall allow users to organize bookmarks into custom collections/folders
- The system shall allow users to remove bookmarks
- Bookmarks are private to each user; bookmark counts visible only to administrators

**FR24: Publication Workflow**
- The system shall support three publication states: Published, Hidden, Pending
- **Published**: Content visible to all users (guests see limited, authenticated see full)
- **Hidden**: Content invisible to regular users, visible only to administrators
- **Pending**: Content awaiting administrator review before publication
- The system shall allow administrators to transition content between states
- The system shall provide notification when content moves to Published state
- The system shall provide queue/list view of Pending content for administrator review
- Only administrators can upload content; registered users can only comment

**FR24A: File Synchronization and Integrity Monitoring**
- The system shall periodically verify that registered publication file paths still exist on disk
- The system shall flag publications as "File Missing" when source files are not found
- The system shall detect new files appearing in monitored folder paths
- The system shall notify administrators of file integrity issues via dashboard alerts
- The system shall provide admin interface to re-link publications to moved files
- The system shall optionally auto-catalog new files discovered in designated monitored folders
- The system shall log all file system changes affecting publications (missing, moved, new)
- The system shall allow administrators to configure file sync check frequency (hourly, daily, weekly)
- The system shall provide bulk re-scan capability to verify all file paths
- The system shall handle gracefully scenarios where storage volumes are temporarily unmounted

**FR24B: Folder Structure Metadata Mapping**
- The system shall allow administrators to define folder path pattern rules for automatic metadata extraction
- The system shall support pattern syntax: `/base/category/subcategory/*.ext` maps to metadata values
- Pattern examples: `/books/sci-fi/*.pdf` → Type="Book", Category="Science Fiction"
- The system shall automatically suggest metadata based on file location during registration
- The system shall allow administrators to override auto-detected folder-based metadata
- The system shall apply folder mapping rules during bulk scanning operations
- The system shall validate and test folder pattern rules before activation
- The system shall support multiple rules with priority ordering
- The system shall provide rule matching preview (show which files match which rules)
- The system shall allow enabling/disabling rules without deletion

### 2.2 Non-Functional Requirements

**NFR1: Performance**
- The system shall load search results within 2 seconds for queries returning up to 1000 results
- The system shall support concurrent file registrations and uploads without degradation (up to 10 simultaneous administrators)
- The system shall handle bulk upload of up to 500 files in a single batch
- Folder scanning operations shall process at minimum 100 files per minute
- File synchronization checks shall complete within 5 minutes for 100,000 files
- Page load times shall not exceed 3 seconds for content browsing pages
- The system shall efficiently manage 1.1TB+ of file storage with minimal database overhead
- View/like/download counters shall update without blocking user interactions (async processing)

**NFR2: Scalability**
- The system shall support storage management of at least 1.1TB of files with room for 50% growth
- The system shall efficiently handle 100,000+ content items without performance degradation
- The system shall support at least 1,000 concurrent users
- Database queries shall be optimized with appropriate indexes
- The system shall use pagination for all list views (default 15-20 items per page)
- The system shall implement caching strategy for frequently accessed data (popular content, statistics)

**NFR3: Security**
- The system shall use Laravel's built-in CSRF protection for all forms
- The system shall sanitize all user inputs to prevent XSS attacks
- The system shall use Eloquent ORM to prevent SQL injection
- The system shall hash all passwords using bcrypt (Laravel default)
- The system shall implement rate limiting on authentication endpoints
- File uploads shall be validated for type and size to prevent malicious uploads
- The system shall enforce role-based access control throughout
- File download access direct for authenticated users when enabled by admin (no signed URLs needed initially)
- The system shall implement rate limiting on download endpoints to prevent abuse

**NFR4: Usability**
- The interface shall be intuitive requiring minimal training for basic operations
- The system shall provide helpful error messages with actionable guidance
- The system shall support keyboard navigation for accessibility
- The system shall provide contextual help/tooltips for complex features
- Forms shall provide real-time validation feedback

**NFR5: Maintainability**
- Code shall follow PSR-12 coding standards
- All models shall use Eloquent relationships properly defined
- All business logic shall be encapsulated in service classes (not controllers)
- The system shall include comprehensive inline documentation
- Database migrations shall be reversible where possible

**NFR6: Reliability**
- The system shall have 99.5% uptime during business hours
- Failed bulk uploads shall not corrupt existing data
- The system shall implement database transactions for multi-step operations
- The system shall log all errors with sufficient context for debugging
- The system shall handle file system failures gracefully (missing files, storage full)
- File synchronization failures shall be logged and reported without blocking user operations
- Missing file detection shall not delete publication records (flag for admin review)
- Folder scanning errors shall not prevent partial results from being saved

**NFR7: Localization**
- All translatable strings shall be stored in Laravel language files (JSON format)
- The system shall support RTL (right-to-left) text rendering for Hebrew
- Date/time formats shall adapt to user locale
- Number formats shall adapt to user locale

**NFR8: Browser Compatibility**
- The system shall support latest versions of Chrome, Firefox, Safari, Edge
- The system shall be responsive (mobile, tablet, desktop)
- The system shall provide graceful degradation for older browsers

**NFR9: Data Integrity**
- The system shall enforce foreign key constraints at database level
- The system shall validate all data before saving
- Soft-deleted records shall remain recoverable for minimum 90 days
- The system shall prevent orphaned records through cascade rules
- Engagement metrics (views, likes, downloads) shall be stored atomically to prevent race conditions

**NFR10: Backup and Recovery**
- Database backups shall be automated daily
- File system backups handled externally (not application responsibility)
- System shall support point-in-time recovery for database
- Backup strategy shall account for 1.1TB+ file storage volume

### 2.3 Compatibility Requirements

**CR1: Laravel Ecosystem Compatibility**
- Must remain compatible with Laravel 12 LTS lifecycle and upgrade path to future versions
- Must use Laravel-supported packages for core functionality

**CR2: Database Schema Integrity**
- Existing database migrations must not be broken by new features
- New features must extend schema through additional migrations, not modify existing ones
- Foreign key relationships in existing models must be preserved

**CR3: Livewire Component Consistency**
- All new interactive UI must follow Livewire 3 patterns matching existing PublicationList and PublicationForm components
- Component communication must use Livewire events and properties
- No mixing of Livewire with traditional JavaScript frameworks

**CR4: Authentication System Integration**
- New role/permission features must build on Laravel Breeze foundation
- Must not replace or break existing authentication flows
- User model extensions must maintain compatibility with Breeze migrations

**CR5: UI/UX Consistency**
- Must use Tailwind CSS classes consistently with existing components
- New pages must follow established layout patterns (AppLayout, GuestLayout)
- Color scheme and typography must remain consistent

**CR6: File Storage Architecture**
- Must use Laravel's filesystem abstraction (Storage facade)
- Must maintain path-based reference system (store paths, not files in DB)
- Must support configuration of storage location via .env
- Local disk storage at 1.1TB scale on dedicated volume
- Must efficiently handle large storage volumes without database bloat

**CR7: Multi-Language Architecture**
- Must use Laravel localization system (lang/ directory structure)
- Must integrate with existing SetLocale middleware pattern
- Must support locale switching without session loss

---

## 3. Technical Constraints and Integration Requirements

### 3.1 Existing Technology Stack

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

### 3.2 Integration Approach

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

### 3.3 Code Organization and Standards

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

### 3.4 Deployment and Operations

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

### 3.5 Risk Assessment and Mitigation

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

## 4. Epic and Story Structure

### 4.1 Epic Approach

**Epic Structure Decision**: **Single Comprehensive Epic** with sequential story breakdown.

**Rationale:**
Given this is building a cohesive library management system on an existing Laravel foundation, a single epic makes sense because:

1. **Unified Feature Set**: All features (search, upload, engagement, multi-language) work together as one integrated system. Splitting into multiple epics would create artificial boundaries.

2. **Shared Infrastructure**: Core models, relationships, and services are interdependent. Stories naturally sequence based on technical dependencies (must have Publication model working before adding comments to publications).

3. **Brownfield Context**: The existing Laravel foundation (models, migrations, Livewire components) provides a stable base. We're extending it incrementally, not building separate subsystems.

4. **User Perspective**: From user's POV, this is "the library system"—not "the search system + the upload system + the engagement system". Single epic reflects this unified product vision.

---

## 5. Epic 1: Modern Library Management System

**Epic Goal**:
Build a complete, modern library management system that enables administrators to efficiently manage and organize a large-scale content library (1.1TB, 100k+ items) while providing users with powerful search, filtering, and engagement features across books, magazines, articles, and custom content types, with full trilingual support (English, Russian, Hebrew).

**Integration Requirements**:
- Extend existing Laravel 12 foundation without breaking existing models or migrations
- Integrate with current Livewire 3 component patterns (PublicationList, PublicationForm)
- Build upon Laravel Breeze authentication system
- Leverage existing SetLocale middleware for multi-language support
- Maintain compatibility with existing database schema through additive migrations only

---

### Story Sequencing Strategy

**Principles:**
1. **Foundation First**: Stories that establish core infrastructure (models, relationships) before feature stories
2. **Risk Mitigation**: Stories that touch existing code come early (while system is simple) to identify integration issues
3. **Incremental Value**: Each story delivers testable value while maintaining system integrity
4. **Dependency Order**: Technical dependencies explicit (e.g., can't add comments before publications display)
5. **Testing Integrated**: Each story includes verification that existing features still work

---

### Story 1.1: Content Model Refinement and Relationships

**As a** developer,
**I want** to refine existing models and establish all core relationships,
**so that** the application has a solid data foundation for all features.

**Acceptance Criteria:**
1. Existing models (Publication, Author, AuthorGroup, Publishing, Magazine, Theme, ThemeSet, Part, PartSet, IssueType, File) have all relationships properly defined and tested
2. Many-to-many relationships use proper pivot tables (not comma-separated values)
3. All models include soft delete trait (`SoftDeletes`) where applicable
4. Models include proper accessors/mutators for computed fields (e.g., word count, formatted dates)
5. Factory classes exist for all models to support testing
6. Comprehensive feature tests verify all relationships work bidirectionally
7. Database indexes added for foreign keys and frequently queried columns

**Integration Verification:**
- IV1: Existing PublicationList and PublicationForm components continue to function without errors
- IV2: Database migrations run successfully on fresh database (no conflicts)
- IV3: `php artisan tinker` can navigate relationships (e.g., `Publication::first()->authors`, `Author::first()->publications`)

---

### Story 1.2: Multi-Language Foundation with RTL Support

**As a** user,
**I want** to switch the interface language between English, Russian, and Hebrew,
**so that** I can use the system in my preferred language.

**Acceptance Criteria:**
1. Language switcher component added to main navigation (flags or dropdown)
2. Language preference stored in session for guests, in user profile for authenticated users
3. All existing UI strings migrated to language files (`lang/en.json`, `lang/ru.json`, `lang/he.json`)
4. Hebrew language displays with proper RTL (right-to-left) layout using Tailwind RTL utilities
5. Date and number formats localize based on selected language
6. SetLocale middleware properly applied to all routes
7. Language files include all UI strings with complete translations for all three languages

**Integration Verification:**
- IV1: Switching language updates entire interface without breaking layouts
- IV2: Hebrew RTL mode properly mirrors layouts (navigation, forms, lists)
- IV3: Existing PublicationList and PublicationForm render correctly in all three languages

---

### Story 1.3: Guest vs. Authenticated Access Control

**As a** guest user,
**I want** to browse content with limited details,
**so that** I can explore the library before deciding to register.

**Acceptance Criteria:**
1. Guest users can view publication list with basic info (title, author, category, cover image)
2. Guest users see "Register to access full content" message on publication details page
3. Guest users cannot see download links or file information
4. Authenticated users see full publication details including download links
5. Middleware enforces access control on protected routes
6. Livewire components conditionally render content based on authentication state
7. Clear calls-to-action encourage guests to register

**Integration Verification:**
- IV1: Existing authentication flows (login, register, password reset) continue to work
- IV2: Switching between guest and authenticated states properly updates UI
- IV3: Attempting to access protected resources as guest redirects to login

---

### Story 1.4: Advanced Search with Full-Text Indexing

**As a** user,
**I want** to search for content by title, author, or description,
**so that** I can quickly find relevant publications.

**Acceptance Criteria:**
1. Search bar prominent in main navigation and on content listing pages
2. MySQL FULLTEXT indexes added to `publications.title`, `publications.description`, `authors.name`
3. Search query returns results ranked by relevance
4. Search supports partial word matching and handles special characters
5. Search works across all three languages (English, Russian, Hebrew)
6. Search results display with matched text highlighted
7. Search provides feedback for zero results ("No results found for 'xyz'")
8. Guests can search but see limited result details

**Integration Verification:**
- IV1: Existing publication list pagination and sorting still work after search integration
- IV2: Search performance acceptable on test dataset (< 2 seconds for 1000 results)
- IV3: Search with non-Latin characters (Russian Cyrillic, Hebrew) returns correct results

---

### Story 1.5: Multi-Criteria Filtering System

**As a** user,
**I want** to filter publications by category, author, date, genre, and text size,
**so that** I can narrow down results to exactly what I need.

**Acceptance Criteria:**
1. Filter sidebar/panel with collapsible sections for each filter type
2. Filters available: Category (hierarchical multiselect), Author (autocomplete), Date (range picker), Genre (multiselect), Text Size (slider with ranges), Alphabetical (A-Z/Z-A), Publication Status (Published/Hidden/Pending - admin only)
3. Multiple filters combine with AND logic (all conditions must match)
4. Applied filters display as removable tags/chips
5. "Clear all filters" button resets to unfiltered view
6. Filter state persists across pagination
7. URL parameters reflect active filters (shareable filtered URLs)
8. Filters update result count in real-time (Livewire reactivity)

**Integration Verification:**
- IV1: Filters work correctly with existing search functionality (search + filter)
- IV2: Pagination maintains filter state across page changes
- IV3: Filter performance acceptable on large dataset (100k+ items)

---

### Story 1.6: File Registration with Optional Upload and Validation

**As an** administrator,
**I want** to register existing files on disk and optionally upload new files,
**so that** I can catalog the 1.1TB library and add new content when needed.

**Acceptance Criteria:**
1. Admin can browse server filesystem via folder tree interface (starting from configured base path)
2. Folder browser displays: folders, files (with format icons), file sizes, modification dates
3. Unregistered files highlighted distinctly from already-registered files
4. Admin can select individual file(s) and click "Register as Publication"
5. Registration form pre-populates with filename-based metadata suggestions
6. Admin can also upload new files via traditional upload form (single file)
7. Upload accepts PDF, EPUB, TXT, DOCX file types
8. File size validation (max 500MB per file)
9. File type validation (MIME type checking, not just extension)
10. Uploaded files stored in organized directory structure (`storage/app/content/{type}/`)
11. Only file path stored in database (not file contents)
12. Unique filename generation for uploads prevents collisions
13. Upload progress indicator for large files
14. Duplicate file path detection prevents registering same file twice
15. Registration creates draft publication record with "Pending" status

**Integration Verification:**
- IV1: File storage path configuration via `.env` works correctly for both registration and upload
- IV2: Registration handles filesystem errors gracefully (permission denied, path not found)
- IV3: Uploaded files accessible via storage filesystem abstraction (`Storage::get()`)
- IV4: Folder browser performs well with directories containing 1000+ files

---

### Story 1.7: Bulk Folder Scanning and Cataloging

**As an** administrator,
**I want** to scan entire folders of existing files and catalog them in bulk,
**so that** I can efficiently register the entire 1.1TB library.

**Acceptance Criteria:**
1. Bulk scan interface allows admin to select folder path from server filesystem
2. Admin can configure scan options: recursive (include subfolders), file format filters, max depth
3. Scan runs as background queue job (async processing)
4. System recursively processes all files in selected folder and subfolders
5. Folder structure preserved as organizational metadata (original path stored)
6. Each discovered file processed as separate queue job
7. Bulk scan dashboard shows real-time progress: "Processing: 450/2,847 files | Success: 420 | Skipped: 25 | Failed: 5"
8. Already-registered files automatically skipped (no duplicates)
9. Failed registrations listed with error reasons (invalid format, size exceeded, read permission denied)
10. Successful scans create draft publication records with "Pending" status
11. Bulk scan summary report generated after completion (total found, registered, skipped, failed, processing time)
12. Admin can pause/cancel bulk scan in progress
13. Scan results filterable (show only errors, show only new registrations)
14. Admin can bulk-approve scanned publications or review individually

**Integration Verification:**
- IV1: Queue worker processes scan jobs successfully (`php artisan queue:work`)
- IV2: Failed jobs stored in `failed_jobs` table with full context for debugging
- IV3: Large bulk scans (10,000+ files) don't crash, timeout, or cause memory issues
- IV4: Scan progress updates visible in real-time via Livewire
- IV5: Canceling scan stops new jobs and doesn't corrupt existing data

---

### Story 1.8: Automatic Metadata Extraction with Admin Confirmation

**As an** administrator,
**I want** the system to automatically extract metadata from uploaded files,
**so that** I don't have to manually enter titles, authors, and publication dates.

**Acceptance Criteria:**
1. Metadata extraction runs as background queue job after file registration or upload
2. Extracts: Title, Author(s), Publication Year, Publisher, ISBN/DOI (if available)
3. Extraction rules configurable per content type (Books use ISBN patterns, Articles use DOI patterns, etc.)
4. Extracted metadata presented to admin for review and confirmation
5. Admin can accept, edit, or reject extracted metadata
6. Extraction status tracked (Pending, Processed, Failed, Confirmed)
7. Manual metadata entry always available as fallback
8. Extraction errors logged with file context for debugging
9. Extraction supports multiple document formats (PDF text extraction, DOCX metadata, EPUB metadata)

**Integration Verification:**
- IV1: Metadata extraction jobs process without blocking user requests
- IV2: Failed extraction doesn't prevent publication creation (falls back to manual entry)
- IV3: Extracted metadata populates correct fields in publication form
- IV4: Extraction works identically for registered files and uploaded files

---

### Story 1.6A: Folder Browser and File Discovery Interface

**As an** administrator,
**I want** an intuitive folder browsing interface to explore server files,
**so that** I can easily discover and register existing content from the 1.1TB library.

**Acceptance Criteria:**
1. Folder browser component displays hierarchical folder tree view
2. Starting path configurable via environment variable (e.g., `/mnt/library-storage`)
3. Folder tree shows: folder icons, file icons (by type), file sizes, modification dates
4. Visual indicators distinguish: unregistered files, registered files, unsupported formats, broken paths
5. Admin can expand/collapse folders to navigate tree
6. Admin can filter view: "Show only unregistered", "Show only [format]", "Show files > [size]"
7. Breadcrumb navigation shows current path
8. Multi-select capability (checkboxes) for bulk file selection
9. Selected files show count: "23 files selected (145 MB total)"
10. Quick actions on selected files: "Register Selected", "Preview Metadata", "Export File List"
11. Search within current folder (filename search)
12. Sort options: Name, Size, Date Modified, Registration Status
13. Pagination or virtual scrolling for folders with 1000+ files
14. Refresh button to rescan current folder for changes
15. Performance: Folder loads within 2 seconds even with 1000 files

**Integration Verification:**
- IV1: Folder browser respects filesystem permissions (doesn't show inaccessible folders)
- IV2: Clicking "Register Selected" opens registration form with all selected files
- IV3: Browser handles symbolic links and mounted volumes correctly
- IV4: Real-time registration status updates (when file registered elsewhere, browser reflects it)

---

### Story 1.6B: File Synchronization and Integrity Monitoring

**As an** administrator,
**I want** the system to monitor file integrity and detect missing or new files,
**so that** I can maintain accurate catalog when files are moved or added outside the application.

**Acceptance Criteria:**
1. Background scheduled job runs file synchronization checks (frequency configurable: hourly, daily, weekly)
2. Sync job verifies each registered publication's file path still exists on disk
3. Missing files flagged in publication record with status "File Missing" and timestamp
4. Publications with missing files highlighted in admin publication list (warning icon)
5. Admin dashboard shows count: "⚠️ 12 publications with missing files"
6. Admin can view "File Issues" report listing all missing files with last known path
7. Admin interface to manually re-link publication to new file path
8. Bulk re-link capability: "Find files with same name in [folder]"
9. Optionally monitor designated "watch folders" for new files appearing
10. New files discovered in watch folders trigger notification to admin
11. Admin can review discovered files and bulk-catalog them
12. Sync job logs all changes: files verified, files missing, files recovered, new files found
13. Admin can manually trigger full rescan: "Verify all file paths now"
14. Sync handles gracefully: temporarily unmounted volumes, network storage issues
15. Performance: Full sync of 100,000 files completes within 5 minutes

**Integration Verification:**
- IV1: Sync job runs on schedule without blocking other operations
- IV2: Missing file flags don't break publication display (graceful degradation)
- IV3: Re-linking file immediately updates publication status and clears warning
- IV4: Watch folders detect new files within configured interval (e.g., 1 hour)

---

### Story 1.6C: Folder Structure Metadata Mapping Rules

**As an** administrator,
**I want** to define rules that extract metadata from folder paths automatically,
**so that** the system can suggest categories and metadata based on file location.

**Acceptance Criteria:**
1. Admin interface for creating/editing folder metadata mapping rules
2. Rule definition includes: Path Pattern (regex or glob), Target Metadata Fields, Values
3. Example rule: Pattern: `/books/sci-fi/**/*.pdf` → Type: "Book", Category: "Science Fiction", Genre: "Sci-Fi"
4. Supported metadata targets: Content Type, Category, Genre, Publication Status, Custom Fields
5. Pattern syntax supports wildcards: `*` (any file), `**` (recursive folders), `{term1,term2}` (alternatives)
6. Admin can test rule against existing files to preview matches
7. Rule test shows: "This rule matches 1,247 files" with sample file list
8. Multiple rules can be defined with priority ordering (drag-to-reorder)
9. Rules evaluated in priority order (first match wins)
10. During file registration/scan, matching rules auto-populate metadata suggestions
11. Admin can always override auto-suggested metadata
12. Rules can be enabled/disabled without deletion
13. Rule edit history tracked (created by, modified by, timestamps)
14. Bulk apply rules: "Apply Rule #3 to all unprocessed files in /books/sci-fi"
15. Rule validation prevents invalid patterns or circular logic

**Integration Verification:**
- IV1: Rules apply correctly during single file registration (Story 1.6)
- IV2: Rules apply correctly during bulk folder scanning (Story 1.7)
- IV3: Rule changes don't retroactively affect already-processed publications
- IV4: Complex path patterns (nested folders, multiple wildcards) match correctly
- IV5: Rule priority ordering works as expected (higher priority rules override lower)

---

### Story 1.9: Custom Content Types and Dynamic Fields

**As an** administrator,
**I want** to define custom content types with unique metadata fields,
**so that** I can adapt the system to different types of publications beyond the default types.

**Acceptance Criteria:**
1. Admin interface for creating/editing content types (name, icon, description)
2. Admin can define custom fields per content type (field name, type, required, visibility)
3. Supported field types: Text, Number, Date, Dropdown, Multiselect, Boolean, Long Text
4. Custom fields appear in publication form when content type selected
5. Custom field values stored in polymorphic `custom_field_values` table
6. Custom fields searchable and filterable (if marked as public)
7. Custom fields display on publication detail page
8. Existing content types (Books, Magazines, Articles, Other) can be extended with custom fields

**Integration Verification:**
- IV1: Adding custom fields doesn't break existing publication CRUD operations
- IV2: Custom field values validate correctly based on field type
- IV3: Custom fields display properly in all three languages

---

### Story 1.10: Publication Detail Page with Engagement Metrics

**As a** user,
**I want** to view comprehensive publication details including cover, description, metadata, and engagement stats,
**so that** I can learn about the publication before accessing it.

**Acceptance Criteria:**
1. Publication detail page displays: Cover image, Title, Author(s), Description, Publication Date, Category, Genre, File size, Word count
2. Engagement metrics visible: View count, Like count, Download count, Comment count, Bookmark count (admin only)
3. Authenticated users see "Download" button (if published and admin-enabled)
4. Authenticated users see Like, Bookmark, and Comment buttons
5. Guests see limited details with "Register to access" CTA
6. Related publications suggested (same author, same category)
7. Publication detail page supports all three languages
8. Breadcrumb navigation shows path to publication

**Integration Verification:**
- IV1: Publication detail page loads within 3 seconds for typical record
- IV2: Metrics display correctly without N+1 query problems (eager loading)
- IV3: Clicking author name navigates to author page with their publications

---

### Story 1.11: View Tracking and Analytics

**As an** administrator,
**I want** to track how many times each publication is viewed,
**so that** I can understand which content is most popular.

**Acceptance Criteria:**
1. View count increments when authenticated user accesses publication detail page
2. Same user can only increment view count once per 24 hours (throttling)
3. View count displayed on publication detail and list views
4. View tracking handled by queue job (async, doesn't block page load)
5. Admin dashboard shows "Most Viewed" publications (top 10)
6. View count stored in `content_views` table with timestamp and user ID
7. View count denormalized to `publications.view_count` for performance

**Integration Verification:**
- IV1: View tracking doesn't slow down publication detail page load
- IV2: View count throttling works (same user viewing within 24h doesn't double-count)
- IV3: View count correctly reflects total unique views

---

### Story 1.12: Like System for Publications

**As a** registered user,
**I want** to like publications I enjoy,
**so that** I can bookmark favorites and support popular content.

**Acceptance Criteria:**
1. Like button (heart icon) on publication detail page and list items
2. Clicking like toggles between liked/unliked state
3. Like count displayed next to button
4. User's liked publications listed in their profile ("My Likes")
5. Like button disabled for guests with "Login to like" tooltip
6. Like action handled instantly (optimistic UI) with background queue job
7. Admin dashboard shows "Most Liked" publications
8. Like state persists across sessions

**Integration Verification:**
- IV1: Like button works without page refresh (Livewire reactivity)
- IV2: Like count updates in real-time for all users viewing same publication
- IV3: Unlike action properly decrements counter and removes from user's list

---

### Story 1.13: Download Tracking System

**As an** administrator,
**I want** to track when users download publications,
**so that** I can measure content utilization and popularity.

**Acceptance Criteria:**
1. Download count increments when user clicks download button
2. Download history logged with timestamp, user ID, and publication ID
3. Download count displayed on publication detail page
4. Admin can enable/disable downloads per publication
5. Download button hidden if admin has disabled downloads
6. Direct file access for authenticated users when enabled by admin
7. Rate limiting applied to downloads (prevent abuse)
8. Admin dashboard shows "Most Downloaded" publications

**Integration Verification:**
- IV1: Download link serves correct file without exposing file system paths
- IV2: Disabled downloads properly hide button and prevent direct URL access
- IV3: Download count accurately reflects successful downloads (not failed attempts)

---

### Story 1.14: Comment System with Moderation

**As a** registered user,
**I want** to comment on publications,
**so that** I can share thoughts and discuss content with other users.

**Acceptance Criteria:**
1. Comment section displayed at bottom of publication detail page
2. Authenticated users can submit comments (text only, up to 5000 characters)
3. Comments display with user name, timestamp, and comment text
4. Users can edit or delete their own comments
5. Threaded comments support (replies to comments)
6. Comment upvoting system (users can upvote helpful comments)
7. Admin moderation queue shows all comments with approve/delete actions
8. Rate limiting: 10 comments per hour per user
9. Comments display chronologically or by popularity (most upvotes)

**Integration Verification:**
- IV1: Comment submission doesn't refresh page (Livewire)
- IV2: Deleted comments remove from display immediately
- IV3: Admin moderation actions (approve/delete) work correctly

---

### Story 1.15: Private Bookmark System

**As a** registered user,
**I want** to bookmark publications for later reading,
**so that** I can easily return to content that interests me.

**Acceptance Criteria:**
1. Bookmark button (bookmark icon) on publication detail page and list items
2. Clicking bookmark toggles between bookmarked/unbookmarked state
3. User's bookmarks private (only visible to that user and admins)
4. "My Bookmarks" page in user profile lists all bookmarked publications
5. Users can organize bookmarks into custom collections/folders (optional enhancement)
6. Bookmark count visible to admins only
7. Bookmark action instant (optimistic UI, no page reload)
8. Guests see "Login to bookmark" message

**Integration Verification:**
- IV1: Bookmark state persists across sessions
- IV2: Unbookmarking removes from "My Bookmarks" list immediately
- IV3: Bookmark counts only visible in admin dashboard, not to regular users

---

### Story 1.16: Publication Status Workflow (Published/Hidden/Pending)

**As an** administrator,
**I want** to control publication visibility with status states,
**so that** I can manage content before making it public.

**Acceptance Criteria:**
1. Three publication states: Published (visible to all), Hidden (admin only), Pending (awaiting review)
2. Admin can set status during publication creation or via edit form
3. Status filter available on admin publication list
4. Published content appears in user search/browse results
5. Hidden content invisible to regular users, visible in admin views
6. Pending content listed in admin "Review Queue" with quick approve/reject actions
7. Status transitions logged in publication history
8. Status change notifications sent to content creator (optional)

**Integration Verification:**
- IV1: Changing publication status immediately updates visibility in search results
- IV2: Hidden publications inaccessible via direct URL for regular users
- IV3: Admin review queue displays accurate count of pending publications

---

### Story 1.17: Author Profile Pages

**As a** user,
**I want** to view author profile pages showing their biography and all published works,
**so that** I can explore content by favorite authors.

**Acceptance Criteria:**
1. Author profile page displays: Name, Photo (optional), Biography (optional), Social links (optional)
2. List of all publications by that author (filtered to Published status for regular users)
3. Author statistics: Total works, Total views across all works
4. Author page accessible by clicking author name anywhere in system
5. Admin can edit author profile information
6. Author profile supports multilingual biographies
7. "No biography available" message if author profile incomplete

**Integration Verification:**
- IV1: Author profile page lists all publications correctly (respects publication status)
- IV2: Author statistics accurately aggregate from publications
- IV3: Author profile page performs well even for prolific authors (50+ works)

---

### Story 1.18: Admin Dashboard with Statistics and Analytics

**As an** administrator,
**I want** a comprehensive dashboard showing system statistics and analytics,
**so that** I can monitor system health and content performance.

**Acceptance Criteria:**
1. Dashboard displays: Total publications, Total users, Total downloads, Total views, Storage usage (GB/TB)
2. Trending content widget: Most viewed (last 7 days), Most liked, Most downloaded
3. Recent activity feed: New registrations, Recent uploads, Recent comments
4. Content breakdown by type (Books: 45%, Magazines: 30%, Articles: 20%, Other: 5%)
5. Category distribution chart
6. Pending review queue count with quick link
7. Failed queue jobs count with quick link
8. Storage usage warning if > 80% capacity
9. Dashboard refreshes statistics daily (cached for performance)

**Integration Verification:**
- IV1: Dashboard loads within 3 seconds (statistics pre-aggregated/cached)
- IV2: Dashboard statistics match actual database counts (verified in tests)
- IV3: Dashboard widgets responsive and functional on mobile devices

---

### Story 1.19: Configurable Metadata Extraction Rules

**As an** administrator,
**I want** to configure metadata extraction rules per content type,
**so that** the system accurately extracts relevant metadata from different document types.

**Acceptance Criteria:**
1. Admin interface for managing extraction rules (per content type)
2. Rule types: Regex patterns, Delimiter-based extraction, Metadata field mapping
3. Admin can define: Start delimiter, End delimiter, Regex pattern, Target field
4. Example rules provided for common patterns (ISBN for books, DOI for articles, ISSN for magazines)
5. Admin can test extraction rules on sample files before saving
6. Rules prioritized (order of execution configurable)
7. Failed extraction logged with rule that failed for debugging
8. Admin can enable/disable individual rules without deleting

**Integration Verification:**
- IV1: Custom extraction rules apply correctly to uploaded files
- IV2: Rule testing interface accurately previews extraction results
- IV3: Changes to extraction rules don't affect already-extracted metadata (only new uploads)

---

### Story 1.20: User Profile and Preferences

**As a** registered user,
**I want** to manage my profile and preferences,
**so that** I can customize my experience and track my activity.

**Acceptance Criteria:**
1. User profile page displays: Username, Email, Registration date, Language preference
2. "My Activity" section: Total views, Total downloads, Total comments, Total likes, Total bookmarks
3. Tabs: "My Likes" (liked publications), "My Bookmarks" (bookmarked publications), "My Comments" (comment history)
4. User can change language preference (persists across sessions)
5. User can change password
6. User can update email (with verification)
7. User can delete account (soft delete, with confirmation)

**Integration Verification:**
- IV1: Profile changes save correctly and reflect immediately
- IV2: Activity counters accurately reflect user actions
- IV3: Account deletion soft-deletes user and preserves data integrity (comments/likes remain)

---

## Story Dependencies

**Visual dependency map:**

```
1.1 (Models) ───┬─→ 1.3 (Access) ─→ 1.4 (Search) ─→ 1.5 (Filters) ─→ 1.10 (Detail)
                │                                                              │
                └─→ 1.2 (Languages) ──────────────────────────────────────────┤
                                                                               │
1.6 (File Reg) ─→ 1.6A (Folder Browser) ─┬─→ 1.7 (Bulk Scan) ─→ 1.8 (Extract)┤
                                          │                                    │
                                          ├─→ 1.6C (Folder Rules) ────────────┤
                                          │                                    │
                                          └─→ 1.6B (File Sync) ───────────────┤
                                                                               │
1.9 (Custom Fields) ────────────────────────────────────────────────────────→┤
                                                                               │
                                                                               ├─→ 1.11 (Views)
                                                                               ├─→ 1.12 (Likes)
                                                                               ├─→ 1.13 (Downloads)
                                                                               ├─→ 1.14 (Comments)
                                                                               ├─→ 1.15 (Bookmarks)
                                                                               ├─→ 1.16 (Workflow)
                                                                               │
                                                                               ├─→ 1.17 (Author Pages)
                                                                               │
1.11-1.17 ──────────────────────────────────────────────────────────────────────→ 1.18 (Dashboard)
                                                                               │
1.8 (Extraction) ───────────────────────────────────────────────────────────────→ 1.19 (Extraction Rules)
                                                                               │
1.11-1.15 ──────────────────────────────────────────────────────────────────────→ 1.20 (User Profile)
```

**Story Sequencing (Updated):**
1. Foundation: 1.1 → 1.2 → 1.3
2. Core Search/Filter: 1.4 → 1.5
3. File Management: 1.6 → 1.6A → 1.6C, 1.6B (parallel) → 1.7 → 1.8
4. Advanced Features: 1.9 → 1.10
5. Engagement: 1.11-1.16 (can be parallel after 1.10)
6. Polish: 1.17 → 1.18 → 1.19 → 1.20

**Note:** Stories 1.6B (File Sync) and 1.6C (Folder Rules) can be developed in parallel after 1.6A. Story 1.6B is optional for MVP and can be deferred to post-MVP if needed.

---

## Implementation Notes

**Estimated Effort:**
- Foundation stories (1.1-1.3): 2-3 weeks
- Core features (1.4-1.5): 1-2 weeks
- File management (1.6, 1.6A-1.6C, 1.7, 1.8): 5-6 weeks
- Advanced features (1.9-1.10): 2-3 weeks
- Engagement features (1.11-1.16): 4-5 weeks
- Polish & analytics (1.17-1.20): 2-3 weeks

**Total: 16-22 weeks for complete implementation**

**Note:** Timeline reflects catalog-first architecture with file sync monitoring and folder-based metadata features. MVP can be achieved in 12-14 weeks by deferring Story 1.6B (File Sync) to post-MVP.

**MVP vs. MVP+1 Breakdown:**

**Core MVP (12-14 weeks):**
- Stories 1.1-1.10 (Foundation, Search, File Management, Detail Pages)
- Story 1.6A (Folder Browser) ✅ Critical
- Story 1.6C (Folder Rules) ✅ High value
- Stories 1.11-1.18 (Engagement & Dashboard)

**MVP+1 Enhancements (2-4 weeks):**
- Story 1.6B (File Sync Monitoring) - Valuable but can be added later
- Stories 1.19-1.20 (Advanced extraction rules, User profiles)

**Next Steps:**
1. Review and approve updated PRD
2. Create architecture document incorporating file scanning/sync design
3. Refine model relationships (Story 1.1)
4. Set up testing infrastructure
5. Begin implementation following updated story sequence

---

**End of PRD**
