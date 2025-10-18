# 5. Epic 1: Modern Seferium Library Management System

**Epic Goal**:
Build a complete, modern library management system for Seferium that enables administrators to efficiently manage and organize a large-scale content library (1.1TB, 100k+ items) while providing users with powerful search, filtering, and engagement features across books, magazines, articles, and custom content types, with full trilingual support (English, Russian, Hebrew).

**Integration Requirements**:
- Extend existing Laravel 12 foundation without breaking existing models or migrations
- Integrate with current Livewire 3 component patterns (PublicationList, PublicationForm)
- Build upon Laravel Breeze authentication system
- Leverage existing SetLocale middleware for multi-language support
- Maintain compatibility with existing database schema through additive migrations only

---

## Story Sequencing Strategy

**Principles:**
1. **Foundation First**: Stories that establish core infrastructure (models, relationships) before feature stories
2. **Risk Mitigation**: Stories that touch existing code come early (while system is simple) to identify integration issues
3. **Incremental Value**: Each story delivers testable value while maintaining system integrity
4. **Dependency Order**: Technical dependencies explicit (e.g., can't add comments before publications display)
5. **Testing Integrated**: Each story includes verification that existing features still work

---

## Story 1.1: Content Model Refinement and Relationships

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

## Story 1.2: Multi-Language Foundation with RTL Support

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

## Story 1.3: Guest vs. Authenticated Access Control

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

## Story 1.3A: Public Root Page & Admin Dashboard Separation

**As a** system architect,
**I want** to establish separate interfaces for public browsing and admin management,
**so that** regular users have a welcoming library experience while admins have powerful management tools.

**Acceptance Criteria:**
1. Root page (`/`) displays clean public library catalog with publication grid layout
2. Root page includes search component and basic filter sidebar (embedded on page, not in navigation)
3. Guest users see "Register to access full content" CTAs on root page
4. Admin dashboard (`/dashboard`) route requires authentication and admin role
5. Admin dashboard includes same search/filter components plus admin-only features (publication status filter)
6. Navigation bar "My Profile" button renamed to "Dashboard" for admin users
7. Navigation bar does NOT include search bar (search lives on pages)
8. User profile page (`/profile`) route created for future personal features (bookmarks, likes)
9. All three languages (EN/RU/HE) supported on all new pages
10. Routes properly protected with authentication and role middleware

**Integration Verification:**
- IV1: Root page accessible to all users (guests and authenticated) without errors
- IV2: Admin dashboard redirects guests to login, blocks non-admin authenticated users with 403
- IV3: Search and filter components work identically on both root and dashboard pages
- IV4: Navigation correctly shows Dashboard (admins) vs Login/Register (guests)

**Tasks / Subtasks:**
- [ ] **Task 1: Create PublicCatalog Livewire component for root page** (AC: 1, 2, 3)
  - [ ] Create `app/Livewire/PublicCatalog.php` component
  - [ ] Create `resources/views/livewire/public-catalog.blade.php` view
  - [ ] Implement publication grid layout using existing PublicationList query logic
  - [ ] Embed GlobalSearch component (from Story 1.4) at top of page
  - [ ] Embed PublicationFilters component (from Story 1.5) as sidebar
  - [ ] Add guest CTAs: "Register to access full content" with login/register buttons
  - [ ] Use GuestLayout for guests, AppLayout for authenticated users
  - [ ] Apply pagination (15 items per page)
  - [ ] Ensure responsive design (grid → list on mobile)

- [ ] **Task 2: Create AdminDashboard Livewire component** (AC: 4, 5)
  - [ ] Create `app/Livewire/Admin/AdminDashboard.php` component
  - [ ] Create `resources/views/livewire/admin/admin-dashboard.blade.php` view
  - [ ] Implement admin management interface with publication list
  - [ ] Embed GlobalSearch component (same as root page)
  - [ ] Embed PublicationFilters component with ALL filters (including Publication Status)
  - [ ] Add admin-specific actions: Bulk actions, Add New, Show Deleted toggle
  - [ ] Use AppLayout
  - [ ] Add statistics cards: Total publications, Pending review count, Recent uploads
  - [ ] Apply role-based middleware (`middleware(['auth', 'role:admin'])`)

- [ ] **Task 3: CLEANUP - Update navigation to remove search, rename "My Profile" → "Dashboard"** (AC: 6, 7)
  - [ ] Modify `resources/views/livewire/layout/navigation.blade.php`
  - [ ] **CLEANUP STEP 1: Delete lines 31-34 (Desktop search bar)**
    - Remove: `<!-- Search Bar (Desktop) -->`
    - Remove: `<div class="hidden sm:flex sm:items-center sm:ms-6">`
    - Remove: `    @livewire('search.global-search')`
    - Remove: `</div>`
  - [ ] **CLEANUP STEP 2: Delete lines 141-144 (Mobile search bar)**
    - Remove: `<!-- Search Bar (Mobile) -->`
    - Remove: `<div class="px-4 pt-4 pb-3 border-b border-gray-200 dark:border-gray-600">`
    - Remove: `    @livewire('search.global-search')`
    - Remove: `</div>`
  - [ ] **CLEANUP STEP 3: Change "My Profile" to "Dashboard" (line 43)**
    - FROM: `{{ __('My Profile') }}`
    - TO: `{{ __('Dashboard') }}`
  - [ ] **CLEANUP STEP 4: Change "My Profile" to "Dashboard" mobile (line 152)**
    - FROM: `{{ __('My Profile') }}`
    - TO: `{{ __('Dashboard') }}`
  - [ ] **VERIFY:** Routes already correct (`route('dashboard')` exists from Laravel Breeze)
  - [ ] Keep existing logic: guests see Login/Register, authenticated see Dashboard/Profile/Logout
  - [ ] Translation files: "Dashboard" key already exists from Story 1.2, no changes needed

- [ ] **Task 4: Create `/profile` route placeholder for future user features** (AC: 8)
  - [ ] Create `app/Livewire/User/UserProfile.php` component
  - [ ] Create `resources/views/livewire/user/user-profile.blade.php` view
  - [ ] Add route: `Route::get('/profile', UserProfile::class)->name('profile')->middleware('auth')`
  - [ ] Display placeholder content: "Your profile page. Future features: Bookmarks, Likes, Comments, Settings"
  - [ ] Add to navigation: "Profile" link (authenticated users only)

- [ ] **Task 5: Update route definitions** (AC: 4, 10)
  - [ ] Modify `routes/web.php`:
    - [ ] Change root route from `welcome` to `PublicCatalog::class`: `Route::get('/', PublicCatalog::class)->name('home')`
    - [ ] Update dashboard route to use AdminDashboard component: `Route::get('/dashboard', AdminDashboard::class)->middleware(['auth', 'role:admin'])->name('dashboard')`
    - [ ] Add profile route: `Route::get('/profile', UserProfile::class)->middleware('auth')->name('profile')`
  - [ ] Verify middleware protection works (guests → login, non-admins → 403 on dashboard)

- [ ] **Task 6: Add translation keys for new pages** (AC: 9)
  - [ ] Update `lang/en.json`: "Dashboard", "Public Catalog", "Profile", "Welcome to Seferium Library", "Explore our collection", "Admin Management", "Statistics"
  - [ ] Update `lang/ru.json`: Same keys translated to Russian
  - [ ] Update `lang/he.json`: Same keys translated to Hebrew
  - [ ] Verify RTL layout works correctly on all new pages

- [ ] **Task 7: Write feature tests for route access control and page rendering** (IV: all)
  - [ ] Create `tests/Feature/PublicCatalogTest.php`:
    - [ ] Test guest can access root page
    - [ ] Test authenticated user can access root page
    - [ ] Test search component renders on root page
    - [ ] Test filters render on root page
    - [ ] Test guest sees "Register" CTAs
  - [ ] Create `tests/Feature/AdminDashboardTest.php`:
    - [ ] Test guest redirected to login when accessing `/dashboard`
    - [ ] Test non-admin user blocked with 403 when accessing `/dashboard`
    - [ ] Test admin user can access `/dashboard`
    - [ ] Test admin sees Publication Status filter
    - [ ] Test search and filters work on dashboard
  - [ ] Create `tests/Feature/UserProfileTest.php`:
    - [ ] Test guest redirected to login when accessing `/profile`
    - [ ] Test authenticated user can access `/profile`
  - [ ] Run full regression test suite to ensure existing stories unaffected

---

## Story 1.4: Advanced Search with Full-Text Indexing

**As a** user,
**I want** to search for content by title, author, or description,
**so that** I can quickly find relevant publications.

**Acceptance Criteria:**
1. ~~Search bar prominent in main navigation and on content listing pages~~ **Search component embedded on root page (`/`) and admin dashboard (`/dashboard`) - NOT in navigation bar** *(Modified per Sprint Change Proposal 2025-10-18)*
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

## Story 1.5: Multi-Criteria Filtering System

**As a** user,
**I want** to filter publications by category, author, date, genre, and text size,
**so that** I can narrow down results to exactly what I need.

**Acceptance Criteria:**
1. Filter sidebar/panel with collapsible sections for each filter type **embedded on root page (`/`) and admin dashboard (`/dashboard`)** *(Modified per Sprint Change Proposal 2025-10-18)*
2. Filters available: Category (hierarchical multiselect), Author (autocomplete), Date (range picker), Genre (multiselect), Text Size (slider with ranges), Alphabetical (A-Z/Z-A), **Publication Status (Published/Hidden/Pending - visible ONLY on `/dashboard`, not on `/`)** *(Modified per Sprint Change Proposal 2025-10-18)*
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

## Story 1.6: File Registration with Optional Upload and Validation

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

## Story 1.7: Bulk Folder Scanning and Cataloging

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

## Story 1.8: Automatic Metadata Extraction with Admin Confirmation

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

## Story 1.6A: Folder Browser and File Discovery Interface

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

## Story 1.6B: File Synchronization and Integrity Monitoring

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

## Story 1.6C: Folder Structure Metadata Mapping Rules

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

## Story 1.9: Custom Content Types and Dynamic Fields

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

## Story 1.10: Publication Detail Page with Engagement Metrics

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

## Story 1.11: View Tracking and Analytics

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

## Story 1.12: Like System for Publications

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

## Story 1.13: Download Tracking System

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

## Story 1.14: Comment System with Moderation

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

## Story 1.15: Private Bookmark System

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

## Story 1.16: Publication Status Workflow (Published/Hidden/Pending)

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

## Story 1.17: Author Profile Pages

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

## Story 1.18: Admin Dashboard with Statistics and Analytics

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

## Story 1.19: Configurable Metadata Extraction Rules

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

## Story 1.20: User Profile and Preferences

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
