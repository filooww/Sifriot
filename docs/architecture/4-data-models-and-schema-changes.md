# 4. Data Models and Schema Changes

## 4.1 New Data Models

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

## 4.2 Schema Integration Strategy

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
