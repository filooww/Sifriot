# Literature Database Manager - Complete Guide

> Your literature database has been fully modernized and is now running on Laravel 11 with Livewire 3!

---

## 📋 Table of Contents
- [What's New](#whats-new)
- [Quick Start](#quick-start)
- [Application Features](#application-features)
- [Development Commands](#development-commands)
- [Database Structure](#database-structure)
- [Project Structure](#project-structure)
- [Configuration](#configuration)
- [Troubleshooting](#troubleshooting)
- [Resources](#resources)

---

## What's New

### Complete Tech Stack Modernization ✅

Your literature database manager has been successfully upgraded and is now fully functional!

### Before vs After

| Component | Old (Legacy) | New (Modern) | Status |
|-----------|--------------|--------------|--------|
| **PHP** | 5.5.14 (EOL 2016) | 8.4.13 | ✅ |
| **Framework** | None (procedural) | Laravel 11 | ✅ |
| **Database** | MySQL 5.6.19 (EOL 2021) | MySQL 8.0 | ✅ |
| **Frontend** | jQuery + inline scripts | Livewire 3 + Alpine.js 3 + Tailwind CSS | ✅ |
| **Web Server** | Apache (manual install) | Apache 2.4 (Docker) | ✅ |
| **Build Tools** | None | Vite + NPM | ✅ |
| **Container** | None | Docker Compose | ✅ |
| **Dependencies** | Manual | Composer + NPM | ✅ |
| **Testing** | None | PHPUnit 11 | ✅ |
| **Migrations** | Raw SQL | Laravel Migrations | ✅ |
| **Models** | None | Eloquent ORM | ✅ |
| **UI** | Legacy PHP templates | Livewire Components | ✅ |

### What's Running Now

**🐳 Docker Containers (3 services)**

1. **literature_web** - PHP 8.4 + Apache + Laravel
   - Port: 8080
   - URL: http://localhost:8080

2. **literature_db** - MySQL 8.0
   - Port: 3306
   - Database: `db_manager`

3. **literature_phpmyadmin** - phpMyAdmin
   - Port: 8081
   - URL: http://localhost:8081

### What's Been Built

✅ **Database Migrations** - All tables migrated to Laravel
- publications (main table)
- authors & author_groups
- themes & theme_sets
- publishings
- parts & part_sets
- magazines
- issue_types
- files

✅ **Eloquent Models** - Full ORM support
- Publication model with relationships
- Author, Theme, Publishing models
- File model with composite key

✅ **Livewire Components** - Modern reactive UI
- PublicationList component with search & pagination
- Delete/restore functionality
- Real-time filtering

✅ **Frontend** - Built and ready
- Tailwind CSS for styling
- Alpine.js for interactivity
- Vite for fast builds

---

## Quick Start

### 1. Access Your Application

- **Web Application**: http://localhost:8080
- **Database Admin (phpMyAdmin)**: http://localhost:8081
- **MySQL**: localhost:3306

### 2. Start the Development Environment

```bash
# Start all containers
docker compose up -d

# Check container status
docker compose ps

# View logs
docker compose logs -f web
```

### 3. Build Frontend Assets

```bash
# Build for production
npm run build

# Or run in watch mode for development
npm run dev
```

### 4. Verify Everything is Working

```bash
# Check migrations
docker compose exec web php artisan migrate:status

# Test database connection
docker compose exec db mysql -u dbuser -pdbpass db_manager -e "SHOW TABLES;"

# Check routes
docker compose exec web php artisan route:list
```

---

## Application Features

### Publications Management

The main interface at http://localhost:8080 provides:

**Search & Filter**
- Search publications by title
- Filter between active and deleted publications
- Real-time search with debouncing

**View Publications**
- Paginated table view (15 per page)
- Display ID, title, authors, publisher, year, type
- Upload date tracking
- Color-coded deleted items

**Actions**
- Delete publications (soft delete)
- Restore deleted publications
- View/Edit buttons (ready for implementation)

### Database Tables

**Core Tables**:
- `publications` - Main publication records
- `authors` - Author names
- `author_groups` - Sets of authors per publication
- `themes` - Theme/category names
- `theme_sets` - Sets of themes per publication
- `publishings` - Publisher information
- `parts` - Series/collection names (hierarchical)
- `part_sets` - Sets of series per publication
- `magazines` - Magazine titles
- `issue_types` - Publication types (book, magazine, article, etc.)
- `files` - File attachments per publication

**Laravel Tables**:
- `users` - User authentication
- `migrations` - Migration tracking
- `cache` - Application cache
- `jobs` - Queue management

---

## Development Commands

### Docker Commands

```bash
# Start containers
docker compose up -d

# Stop containers
docker compose down

# View logs
docker compose logs -f web

# Access web container shell
docker compose exec web bash

# Restart containers
docker compose restart

# Rebuild containers (if needed)
docker compose down -v
docker compose up -d --build
```

### Laravel Artisan Commands

```bash
# Create a new Livewire component
docker compose exec web php artisan make:livewire ComponentName

# Run migrations
docker compose exec web php artisan migrate

# Rollback migrations
docker compose exec web php artisan migrate:rollback

# Fresh migration (drop all + recreate)
docker compose exec web php artisan migrate:fresh

# Create a new migration
docker compose exec web php artisan make:migration create_table_name

# Create a model
docker compose exec web php artisan make:model ModelName

# Run tests
docker compose exec web php artisan test

# Clear cache
docker compose exec web php artisan cache:clear
docker compose exec web php artisan config:clear
docker compose exec web php artisan view:clear

# Interactive debugger (tinker)
docker compose exec web php artisan tinker

# List all routes
docker compose exec web php artisan route:list

# Check Laravel status
docker compose exec web php artisan about
```

### Frontend Development

```bash
# Watch for changes (development with hot reload)
npm run dev

# Build for production
npm run build

# With host access
npm run dev -- --host
```

### Database Commands

```bash
# Access MySQL shell
docker compose exec db mysql -u dbuser -pdbpass db_manager

# Check MySQL version
docker compose exec db mysql -u dbuser -pdbpass -e "SELECT VERSION();"

# Import SQL file
docker compose exec -T db mysql -u dbuser -pdbpass db_manager < file.sql

# Export database
docker compose exec db mysqldump -u dbuser -pdbpass db_manager > backup.sql

# Show all tables
docker compose exec db mysql -u dbuser -pdbpass db_manager -e "SHOW TABLES;"
```

---

## Database Structure

### Publications Table (Main)

```sql
id_publication      INT (PK, Auto-increment)
title              VARCHAR(255)
title_low          VARCHAR(255) -- Lowercase for searching
id_publishing      BIGINT       -- FK to publishings
id_part            BIGINT       -- FK to part_sets (series)
issue_year         CHAR(4)
id_issue_type      BIGINT       -- FK to issue_types
id_magazine        BIGINT       -- FK to magazines
upload_date        DATE
actuality          TINYINT
id_theme_set       BIGINT       -- FK to theme_sets
id_author_set      BIGINT       -- FK to author_groups
_del_mark          TINYINT      -- Soft delete flag
created_at         TIMESTAMP
updated_at         TIMESTAMP
```

### Files Table (Attachments)

```sql
id_publication     BIGINT (PK, FK)
file_name          VARCHAR(255) (PK)
file_name_low      VARCHAR(255)
file_description   VARCHAR(255)
file_issue_year    CHAR(4)
file_volume        CHAR(5)
file_number        CHAR(7)
file_page          CHAR(9)
ord_num            INT
file_size          CHAR(11)
file_source        VARCHAR(255)
created_at         TIMESTAMP
updated_at         TIMESTAMP
```

### Relationships

- **Publication** belongs to: Publishing, Part, IssueType, Magazine, ThemeSet, AuthorGroup
- **Publication** has many: Files
- **File** belongs to: Publication
- **ThemeSet** contains multiple: Themes (comma-separated IDs)
- **AuthorGroup** contains multiple: Authors (comma-separated IDs)
- **PartSet** contains multiple: Parts (comma-separated IDs, hierarchical)

---

## Project Structure

```
/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   ├── Livewire/
│   │   └── Publications/
│   │       ├── PublicationList.php     ← Main component
│   │       └── PublicationForm.php     ← Form component (ready)
│   └── Models/
│       ├── Publication.php              ← With relationships
│       ├── Author.php
│       ├── File.php
│       └── ...
│
├── database/
│   ├── migrations/
│   │   ├── 2025_10_10_*_create_authors_table.php
│   │   ├── 2025_10_10_*_create_publications_table.php
│   │   └── ...
│   └── seeders/
│
├── resources/
│   ├── views/
│   │   ├── components/
│   │   │   └── layouts/
│   │   │       └── app.blade.php       ← Main layout
│   │   └── livewire/
│   │       └── publications/
│   │           └── publication-list.blade.php
│   ├── js/
│   │   └── app.js                       ← Alpine.js setup
│   └── css/
│       └── app.css                      ← Tailwind CSS
│
├── public/
│   └── build/                           ← Compiled assets (Vite output)
│
├── routes/
│   └── web.php                          ← Routes
│
├── docker/
│   ├── php/
│   │   ├── Dockerfile
│   │   └── php.ini
│   └── apache/
│       └── 000-default.conf
│
├── HTDocs_legacy/                       ← Old PHP application (archived)
│   ├── index.php
│   ├── literature.sql
│   └── ...
│
├── docker-compose.yml
├── .env
├── composer.json
├── package.json
└── vite.config.js
```

---

## Configuration

### Environment Variables (`.env`)

```bash
# Application
APP_NAME="Literature Database"
APP_ENV=local
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost:8080

# Database
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=db_manager
DB_USERNAME=dbuser
DB_PASSWORD=dbpass

# Container Ports
WEB_PORT=8080
PMA_PORT=8081
```

### Database Credentials

- **Host**: `db` (inside containers) or `localhost` (from host machine)
- **Port**: `3306`
- **Database**: `db_manager`
- **Username**: `dbuser`
- **Password**: `dbpass`
- **Root Password**: `rootpass`

### Docker Configuration

**PHP Extensions Installed**:
- pdo_mysql, mbstring, exif, pcntl, bcmath, gd, zip, intl

**Apache Modules**:
- mod_rewrite enabled
- DocumentRoot: `/var/www/html/public`

**Node.js**: Included for frontend builds

---

## Troubleshooting

### Container won't start

```bash
# Check logs
docker compose logs web
docker compose logs db

# Rebuild containers
docker compose down -v
docker compose up -d --build
```

### Database connection errors

```bash
# Wait for MySQL to fully start (takes ~30 seconds)
docker compose logs db | grep "ready for connections"

# Test connection
docker compose exec db mysql -u dbuser -pdbpass db_manager -e "SELECT 1;"
```

### Permission issues

```bash
# Fix Laravel storage permissions
docker compose exec web chown -R www-data:www-data /var/www/html/storage
docker compose exec web chmod -R 775 /var/www/html/storage

# Fix bootstrap cache
docker compose exec web chown -R www-data:www-data /var/www/html/bootstrap/cache
docker compose exec web chmod -R 775 /var/www/html/bootstrap/cache
```

### Asset build errors

```bash
# Clean and reinstall
rm -rf node_modules package-lock.json
npm install
npm run build
```

### Page shows "500 Internal Server Error"

```bash
# Check application logs
docker compose logs web

# Clear all caches
docker compose exec web php artisan cache:clear
docker compose exec web php artisan config:clear
docker compose exec web php artisan view:clear

# Regenerate autoload files
docker compose exec web composer dump-autoload
```

### Livewire component not updating

```bash
# Clear Livewire cache
docker compose exec web php artisan livewire:discover

# Rebuild frontend
npm run build
```

---

## Next Steps

### Immediate Tasks

1. ✅ Environment setup
2. ✅ Container deployment
3. ✅ Database migrations
4. ✅ Eloquent models
5. ✅ Livewire components
6. ✅ Frontend build
7. 🔄 Import legacy data
8. 🔄 Add authentication
9. 🔄 Create publication form
10. 🔄 Add file upload functionality
11. 🔄 Write tests
12. 🔄 Deploy to production

### Import Legacy Data

```bash
# Option 1: Import from SQL file
docker compose exec -T db mysql -u dbuser -pdbpass db_manager < HTDocs_legacy/literature.sql

# Option 2: Create seeder from legacy data
docker compose exec web php artisan make:seeder LegacyDataSeeder
# Then implement the seeder and run:
docker compose exec web php artisan db:seed --class=LegacyDataSeeder
```

### Add Authentication

```bash
# Install Laravel Breeze (recommended)
docker compose exec web composer require laravel/breeze --dev
docker compose exec web php artisan breeze:install livewire
npm install
npm run build
docker compose exec web php artisan migrate
```

### Create Publication Form

The `PublicationForm` component is already created. Implement it:

```php
// app/Livewire/Publications/PublicationForm.php
// Add form fields, validation, and save logic
```

---

## Resources

### Project Documentation
- **[📘 LIVEWIRE_GUIDE.md](LIVEWIRE_GUIDE.md)** - Complete guide on how Livewire works, folder structure, and Tailwind CSS integration

### External Documentation
- [Laravel 11 Documentation](https://laravel.com/docs/11.x)
- [Livewire 3 Documentation](https://livewire.laravel.com/docs)
- [Alpine.js Documentation](https://alpinejs.dev/)
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)
- [Docker Compose Documentation](https://docs.docker.com/compose/)

### Legacy Files (Archived)
- **Location**: `HTDocs_legacy/`
- **SQL Schemas**: `HTDocs_legacy/literature.sql`, `HTDocs_legacy/db_manager.sql`
- **Original PHP Code**: Reference for business logic migration

### Key Files to Know
- [routes/web.php](../routes/web.php) - Application routes
- [app/Models/Publication.php](../app/Models/Publication.php) - Main model with relationships
- [app/Livewire/Publications/PublicationList.php](../app/Livewire/Publications/PublicationList.php) - Main UI component
- [docker-compose.yml](../docker-compose.yml) - Container configuration
- [.env](../.env) - Environment variables

---

## Summary

**Migration completed**: October 10, 2025
**Status**: ✅ Fully operational
**Application**: http://localhost:8080
**Ready for**: Development and data import

### What You Have Now

1. ✅ Modern Laravel 11 application
2. ✅ MySQL 8.0 with migrated schema
3. ✅ Livewire UI components
4. ✅ Docker containerized environment
5. ✅ Tailwind CSS styling
6. ✅ Publication management interface
7. ✅ Search and filtering capabilities
8. ✅ Soft delete functionality

### What's Next

1. Import your legacy data
2. Add user authentication
3. Complete the publication form
4. Add file upload/download
5. Implement advanced search
6. Add export functionality
7. Write tests
8. Deploy to production

Everything is ready for you to continue building your modern literature database manager! 🚀
