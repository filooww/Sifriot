# 6. Source Tree

## 6.1 File Organization

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

## 6.2 Integration Guidelines

**File Naming:** Models (PascalCase), Livewire PHP (PascalCase), Livewire views (kebab-case), migrations (timestamp + snake_case)

**Folder Organization:** Feature-based for Livewire, type-based for services/models

**Import/Export:** Services via DI, Livewire via events, relationships via Eloquent

---
