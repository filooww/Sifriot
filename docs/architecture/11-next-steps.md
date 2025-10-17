# 11. Next Steps

## 11.1 Implementation Roadmap

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

## 11.2 Developer Quick Start

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

## 11.3 Key Technical Reminders

- **Always specify foreign/local keys** explicitly in relationships
- **Use FileStorageService** for all filesystem operations
- **Queue jobs** with error handling (`onFailure`)
- **Log to specific channels** (`folder_scan`, `file_sync`)
- **Virtual scrolling** for large directories
- **Test in all three languages** (EN, RU, HE with RTL)
- **Queue worker must be running** for background jobs

## 11.4 Useful Commands

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
