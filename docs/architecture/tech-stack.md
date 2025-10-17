# 3. Tech Stack

## 3.1 Existing Technology Stack

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

## 3.2 New Technology Additions

| Technology | Version | Purpose | Rationale | Integration Method |
|-----------|---------|---------|-----------|-------------------|
| **smalot/pdfparser** | ^2.0 | Extract text from PDFs | Pure PHP, no dependencies | Composer; MetadataExtractorService |
| **phpoffice/phpword** | ^1.0 | Extract from DOCX files | Official library | Composer; MetadataExtractorService |
| **league/flysystem** | ^3.0 | File browsing, integrity | Included in Laravel | Storage facade |
| **laravel/dusk** | Latest | Browser testing | Critical user flow testing | Composer dev; browser tests |
| **brianium/paratest** | Latest | Parallel test execution | Faster test suite | Composer dev; test command |

**Note:** No Laravel Horizon (queue dashboard) or Spatie Permission needed - simpler alternatives sufficient.

---
