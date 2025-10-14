# HTDocs Legacy System

> **Legacy PHP Database Management System (2010-2015)**
> 
> **Status:** Being migrated to Laravel 12 (see parent directory)

---

## 🎯 Quick Overview

This is a **complete database management system** with 186 PHP files managing bibliographic/literature data across 4 MySQL databases with full multi-language support (EN/RU).

### Key Stats

- **📁 Files:** 186 PHP files across 32 modules
- **💾 Databases:** 4 (db_manager, literature, phys_math_contents, trees)
- **📊 Data (test):** 11 publications, 19 authors, 476 UI translations × 3 languages
- **🌍 Languages:** English, Russian & Hebrew
- **🔧 Tech:** PHP 5.5, MySQL 5.6, mysqli, procedural code

---

## 📖 Documentation

### For Everyone

**[MIGRATION_SUMMARY.md](MIGRATION_SUMMARY.md)** - Start here!
- Complete system overview
- What is being migrated
- Current status

### For Developers

**[../docs/DEVELOPER_GUIDE_RU.md](../docs/DEVELOPER_GUIDE_RU.md)** (Russian)
- Laravel developer guide
- Legacy → Laravel code mapping
- Pattern transformations
- **See section: "Карта миграции из Legacy системы"**

**[../docs/MIGRATION_PLAN.md](../docs/MIGRATION_PLAN.md)** (500+ lines)
- Complete technical migration plan
- 6 phases over 20-25 weeks
- Module-by-module breakdown
- Database migration strategy
- Testing procedures

### For Managers/Owners

**[../docs/MIGRATION_STRATEGY_RU.md](../docs/MIGRATION_STRATEGY_RU.md)** (Russian)
- Executive summary
- Timeline: 5 months
- Resource requirements
- Risk management
- ROI and success metrics

---

## 🗂️ System Structure

```
s/                          # Main application (186 files)
├── UserEnter/             # 🔐 Authentication
├── MainTable/             # 📝 Publications CRUD
├── Catalogs/              # 📚 References (18 files!)
├── DataBases/             # 🗄️ DB management
├── Languages/             # 🌍 Multi-language
├── Algorithms/            # 🔍 Text parsing
├── Tree/                  # 🌳 Hierarchical data
└── ... (25 more modules)
```

---

## 🚀 Migration Status

| Phase | Status | Duration |
|-------|--------|----------|
| Phase 0: Analysis | ✅ Complete | - |
| Phase 1: Foundation | ⏳ Planned | 2-3 weeks |
| Phase 2: Core | ⏳ Planned | 4-6 weeks |
| Phase 3: Advanced | ⏳ Planned | 3-4 weeks |
| Phase 4: Admin | ⏳ Planned | 2-3 weeks |
| Phase 5: Testing | ⏳ Planned | 2-3 weeks |
| Phase 6: Deployment | ⏳ Planned | 1-2 weeks |

**Total:** ~5 months

---

## 📋 Quick Reference

### Key Legacy Files

| File | Purpose | Lines |
|------|---------|-------|
| `s/index.php` | Entry point | 89 |
| `s/Utilities/DataBases.php` | DB utilities | ~400 |
| `s/Catalogs/*.php` | Reference data | ~5000+ |
| `db_manager.sql` | System DB dump | 2,080 |
| `literature.sql` | Main DB dump | 845 |

### Module Mapping

| Legacy | Laravel |
|--------|---------|
| `UserEnter/` | Laravel Breeze |
| `MainTable/` | Livewire components |
| `Catalogs/` | Livewire catalogs |
| `Languages/` | Laravel localization |
| `Algorithms/` | Service classes |

---

## ⚠️ Important

### DO NOT DELETE

Keep this code until:
- ✅ Migration complete
- ✅ Data validated
- ✅ 3-6 months stable operation

### Separate Projects

- `/Rosenbrock/` - Independent math module
- `/s/` - Database management system (being migrated)

---

## 🔗 Links

**Laravel App:** `../` (parent directory)
**Documentation:** `../docs/`
**Migration Plan:** [../docs/MIGRATION_PLAN.md](../docs/MIGRATION_PLAN.md)
**Dev Guide:** [../docs/DEVELOPER_GUIDE_RU.md](../docs/DEVELOPER_GUIDE_RU.md)

---

## 📞 Questions?

1. Read [MIGRATION_SUMMARY.md](MIGRATION_SUMMARY.md)
2. Check [../docs/MIGRATION_PLAN.md](../docs/MIGRATION_PLAN.md)
3. Review [../docs/DEVELOPER_GUIDE_RU.md](../docs/DEVELOPER_GUIDE_RU.md)
4. Create an issue

---

**Last Updated:** 2025-10-10
