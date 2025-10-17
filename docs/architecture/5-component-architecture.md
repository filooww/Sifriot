# 5. Component Architecture

## 5.1 New Components

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

## 5.2 Component Interaction

All components use Livewire events for communication (`$dispatch`), service classes for business logic, and policies for authorization. Virtual scrolling implemented via `virtual-scroll.js` for folder browser performance.

Real-time notifications via Laravel events: `FolderScanCompleted`, `FileIntegrityIssueDetected` trigger listeners that send notifications to admins.

---
