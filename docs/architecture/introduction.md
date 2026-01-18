# 1. Introduction

This document outlines the architectural approach for enhancing Sifriot (Library Management System) with a comprehensive modern library management platform featuring advanced search, bulk upload, metadata extraction, engagement features, and multilingual support. Its primary goal is to serve as the guiding architectural blueprint for AI-driven development of new features while ensuring seamless integration with the existing Laravel 12 foundation.

**Relationship to Existing Architecture:**
This document supplements the existing Laravel project architecture by defining how new components will integrate with current models, migrations, and Livewire components. Where conflicts arise between new and existing patterns, this document provides guidance on maintaining consistency while implementing enhancements.

## 1.1 Existing Project Analysis

### Current Project State

- **Primary Purpose:** Sifriot (Library Management System) for managing books, magazines, articles, and publications with hierarchical organization
- **Current Tech Stack:**
  - Backend: Laravel 12, PHP 8.2+, MySQL 8.0
  - Frontend: Livewire 3, Tailwind CSS 3, Alpine.js 3
  - Infrastructure: Docker (PHP 8.4-Apache, MySQL 8.0, phpMyAdmin)
  - Auth: Laravel Breeze
- **Architecture Style:** Monolithic Laravel application with Livewire component-based UI, following MVC patterns with server-side rendering
- **Deployment Method:** Docker Compose multi-container setup (web, database, admin tools)

### Available Documentation

- ✅ Comprehensive PRD ([docs/prd.md](docs/prd.md)) - v1.1 with catalog-first architecture, 23 stories defined
- ✅ Migration documentation (MIGRATION_PLAN.md, MIGRATION_SUMMARY.md, MIGRATION_STRATEGY_RU.md)
- ✅ Developer guides (DEVELOPER_GUIDE_RU.md, QUICKSTART.md, LIVEWIRE_GUIDE.md)
- ✅ Legacy system analysis (HTDocs_legacy/README.md)
- ✅ Tech stack configuration (docker-compose.yml, .env.example)
- ✅ Core config ([.bmad-core/core-config.yaml](.bmad-core/core-config.yaml))

### Identified Constraints

- **Database Schema**: Maintain existing table structure and primary key naming (`id_publication`, `id_author`, etc.)
- **Foreign Key Conventions**: Non-standard FK column naming (e.g., `id_author_set` linking to `author_groups.id_author_group`)
- **Model Relationships**: Some relationships exist but need refinement
- **File Storage**: Large-scale storage (1.1TB) requires local disk storage, path-based references
- **Multi-language**: RTL support needed for Hebrew, locale switching via middleware
- **Queue System**: Database queue driver configured (jobs table exists), needs queue worker process

---
