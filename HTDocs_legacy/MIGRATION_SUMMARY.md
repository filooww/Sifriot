# Legacy System Migration Summary

> **This document summarizes the legacy PHP system and provides links to migration documentation**

---

## 📁 What is HTDocs_legacy?

This directory contains a **complete legacy database management system** built in procedural PHP (circa 2010-2015). The system manages bibliographic/literature databases with multi-language support.

### System Overview

**Size:** 186 PHP files across 32 modules
**Databases:** 4 MySQL databases (db_manager, literature, phys_math_contents, trees)
**Languages:** English, Russian, and Hebrew (3 languages)
**Architecture:** Procedural PHP with mysqli database access

### Core Features

1. **Meta-database management** - Manages multiple databases dynamically
2. **Multi-language support** - All UI text stored in database
3. **Dynamic field configuration** - Fields configurable through UI
4. **Text parsing algorithms** - Extract authors, titles, years from text files
5. **Hierarchical data trees** - Unlimited nesting with state persistence
6. **User authentication** - Priority levels, login attempts limiting
7. **Import/Export** - Complex data import workflows

---

## 🗂️ Directory Structure

```
HTDocs_legacy/
├── Apache/                 # Apache web server (being removed)
├── phpMyAdmin/            # Database admin tool (being removed)
├── Rosenbrock/            # Mathematical optimization module (separate project)
├── s/                     # MAIN APPLICATION (186 PHP files)
│   ├── Administrator/     # Admin functions
│   ├── Alarm/            # Error notifications
│   ├── Algorithms/       # Text parsing engines
│   ├── Catalogs/         # Reference data management (18 files!)
│   ├── Codings/          # Character encoding management
│   ├── Configuration/    # System configuration
│   ├── DataBases/        # Database CRUD operations
│   ├── DeleteMarked/     # Soft delete functionality
│   ├── Fields/           # Dynamic field configuration
│   ├── Languages/        # Language management
│   ├── LocalLanguages/   # Localization utilities
│   ├── LoadToServer/     # Deployment scripts
│   ├── LogProcessing/    # Log analysis
│   ├── MainTable/        # Main data operations
│   │   ├── Update/      # Create/Edit records
│   │   └── View/        # Display data
│   ├── PrimatyUpload/    # File upload & import
│   ├── SystemDataBaseCopies/ # Database backups
│   ├── Tables/           # Table management
│   ├── Titles/           # Interface text management
│   ├── Tree/             # Hierarchical data structures
│   ├── UserEnter/        # Authentication
│   ├── UserList/         # User management
│   ├── Utilities/        # Helper functions (11 files)
│   └── Visits/           # Activity tracking
├── Images/               # UI images (being removed)
└── *.sql                 # Database dumps
```

---

## 📊 Database Schema

### db_manager (System Database)

**Core tables:**
- `user_ident` - User accounts and authentication
- `interface_texts` - ~700 UI translations (EN/RU)
- `languages` - Language registry
- `db_list` - Database registry
- `algorithms` - Text parsing algorithms (~30 configured)
- `coding_table` - Character encodings
- `translate_table` - Character transliteration
- `db_s_configs` - System configuration
- `interface_special_texts` - UI element configurations
- `visits` - User activity logs

### literature (Main Database)

**Core tables:**
- `publications` - Main publications table (~1000+ records)
- `authors` - Author names (~500)
- `author_groups` - Author groupings (many-to-many)
- `publishings` - Publishers (~200)
- `themes` / `theme_sets` - Thematic classification
- `series` - Publication series
- `issue_types` - Types of publications
- `magazines` - Magazine/journal titles
- `parts` / `part_sets` - Hierarchical parts
- `files` - Associated files
- `field_config` - Dynamic field definitions
- `table_definitions` - Catalog metadata
- `collapse_ids` - UI state (tree expansion)

### Additional Databases

- `phys_math_contents` - Physics/mathematics reference content
- `trees` - Generic hierarchical data structures

---

## 🔄 Migration to Laravel 12

**Status:** Planning phase complete ✅

This legacy system is being migrated to a modern Laravel 12 application located in the parent directory (`../`).

### Migration Documentation

Three comprehensive documents have been created:

1. **[../docs/MIGRATION_PLAN.md](../docs/MIGRATION_PLAN.md)** (Technical, 500+ lines)
   - Complete technical migration plan
   - 6 phases over 20-25 weeks
   - Detailed module-by-module mapping
   - Database schema migration strategy
   - Risk assessment and mitigation
   - Testing and validation procedures

2. **[../docs/DEVELOPER_GUIDE_RU.md](../docs/DEVELOPER_GUIDE_RU.md)** (Updated with legacy mapping)
   - Developer guide for Laravel (Russian)
   - Legacy system → Laravel mapping section
   - Code pattern transformations
   - Function-by-function equivalents
   - Quick reference for developers familiar with old system

3. **[../docs/MIGRATION_STRATEGY_RU.md](../docs/MIGRATION_STRATEGY_RU.md)** (Executive summary, Russian)
   - Non-technical overview for stakeholders
   - 5-month timeline
   - Resource requirements
   - Risk management
   - Success metrics
   - Decision-making guide

### Key Migration Principles

1. **Preserve data integrity** - Zero data loss
2. **Maintain compatibility** - Keep table structures initially
3. **Incremental approach** - Migrate module by module
4. **Parallel operation** - Old system continues during migration
5. **Comprehensive testing** - Validate every step

---

## 📋 Migration Phases

### Phase 0: Preparation (1-2 weeks)
- [x] Full system analysis
- [x] Migration plan creation
- [ ] Test environment setup

### Phase 1: Foundation (2-3 weeks)
- Database structure migration
- Laravel models creation
- Basic authentication

### Phase 2: Core Functionality (4-6 weeks)
- Publications CRUD
- Catalogs/references
- Multi-language system
- Configuration management

### Phase 3: Advanced Features (3-4 weeks)
- Text parsing algorithms
- Hierarchical trees
- Data import/export
- Dynamic fields

### Phase 4: Administration (2-3 weeks)
- Database management
- User management
- Logging and monitoring
- Backup systems

### Phase 5: Testing & Optimization (2-3 weeks)
- Performance tuning
- Security audit
- Automated testing
- Documentation

### Phase 6: Deployment (1-2 weeks)
- Production setup
- Data migration
- User training
- Go-live

**Total: 15-23 weeks (~5 months)**

---

## 🗺️ Module Mapping: Legacy → Laravel

| Legacy Module | Laravel Equivalent | Status |
|---------------|-------------------|---------|
| `UserEnter/` | Laravel Breeze + User Model | ⏳ Planned |
| `MainTable/` | Livewire Publications components | 🔄 In Progress |
| `Catalogs/` | Livewire Catalog components | ⏳ Planned |
| `Languages/` | Laravel localization + Middleware | ⏳ Planned |
| `Titles/` | JSON translation files | ⏳ Planned |
| `DataBases/` | Migrations + Artisan commands | ⏳ Planned |
| `Algorithms/` | Service classes | ⏳ Planned |
| `Tree/` | Livewire Tree components | ⏳ Planned |
| `Utilities/` | Service classes + Helpers | ⏳ Planned |

---

## 🔍 Key Technical Insights

### 1. Meta-Database Architecture 🗄️

**What makes it unique:**
The system doesn't just manage data—it **manages databases themselves**. It's a database management system for bibliographic databases.

**Boot sequence:** ([s/index.php:35-48](s/index.php#L35-L48))
```php
// 1. Connect to system database
$dbh = GetManagerDBFile("db_manager", $_SERVER['DOCUMENT_ROOT']."/s/_Credentials.txt");

// 2. Test if system tables exist
TestManagerTablesExist($dbh);

// 3. If tables missing, auto-create them
if (count($_SESSION['preliminary_flags']['no_existed_tables']) > 0) {
    $dbh = GetOnlyDB("db_manager");
}

// 4. Test table structure and auto-repair
$_SESSION['structure_errors'] = TestManagerTableStructure($dbh,
                                   ManagerDataBaseStructureDefinition());
```

**Key features:**
- Dynamic database registry stored in `db_manager.db_list`
- Self-healing with auto-repair functions
- Multi-database connection management
- Runtime table structure validation

**Implementation:** [s/Utilities/DataBases.php:2-35](s/Utilities/DataBases.php#L2-L35)

---

### 2. Configuration-Driven Dynamic UI 🎨

**The innovation:**
Field definitions, form layouts, and validations are **stored in database**, not hardcoded. The UI generates itself based on configuration.

**Field configuration table:** ([s/Fields/FieldUtilities.php:2-24](s/Fields/FieldUtilities.php#L2-L24))
```sql
field_config (
    own_table,      -- Which table this field belongs to (1 or 2)
    f_name,         -- Field name
    f_key,          -- Is primary key? (0/1)
    f_type,         -- Data type (0-8: text, number, date, reference, etc.)
    screen_order,   -- Display order in UI (0 = hidden)
    load_order,     -- Load sequence
    f_size,         -- Field size/max length
    f_interval,     -- Is date range? (0/1)
    f_blank,        -- Allow blank? (0/1)
    f_unique,       -- Must be unique? (0/1)
    f_default,      -- Default value
    f_table,        -- Reference table (for lookups/foreign keys)
    f_illegals,     -- Forbidden characters
    f_check,        -- Enable validation? (0/1)
    comm,           -- Has comments? (0/1)
    f_filter_md,    -- Filter mode (0-2: starts with, contains, equals)
    f_sort_sm,      -- Sort mode (0-2: none, asc, desc)
    f_using,        -- Where used (comma-separated codes)
    f_align,        -- Alignment in table (0-2: left, center, right)
    table_percent   -- Column width (%, px, or number)
)
```

**Dynamic field loader:** ([s/Fields/FieldUtilities.php:25-43](s/Fields/FieldUtilities.php#L25-L43))
```php
function GetFieldParameters($dbh, $mandatory_db_tables) {
    $f_param = array();
    $res = mysqli_query($dbh, "SELECT * FROM field_config");
    while ($row = mysqli_fetch_row($res)) {
        $f_param[$row[0]][$row[1]] = array(
            "f_key"        => ($row[2] == 1),
            "ind_in_t"     => $test_err['ind'] + 1,
            "screen_order" => $row[20],
            "load_order"   => $row[21],
            "f_name"       => $row[3],
            "f_type"       => $row[4],
            "f_unique"     => ($row[8] == 1),
            "f_default"    => $row[12],
            "f_size"       => $row[5],
            // ... 20+ configurable parameters per field
        );
    }
    return $f_param;
}
```

**Auto-discovery of database fields:** ([s/Fields/FieldUtilities.php:187-208](s/Fields/FieldUtilities.php#L187-L208))
```php
function GetAllFieldList($dbh, &$db_err, $db = -1) {
    $arr_table = GetTableList($dbh);
    foreach ($arr_table as $table) {
        // Introspect actual database structure
        $res_field = mysqli_query($dbh, "SHOW FULL COLUMNS FROM ".$table);
        while ($row_field = mysqli_fetch_row($res_field)) {
            $arr_all_field[$table][$row_field[0]] = array(
                $row_field[1],  // Type
                $row_field[2],  // Collation
                $row_field[3],  // Null
                $row_field[4],  // Key
                $row_field[5],  // Default
                $row_field[6]   // Extra (auto_increment, etc.)
            );
        }
    }
    return $arr_all_field;
}
```

**Migration impact:** This allows users to add custom fields without code changes. Must preserve this flexibility in Laravel.

---

### 3. Sophisticated Text Parsing Engine 📝

**The power:**
30+ configurable algorithms that extract structured data from unformatted text files. Can parse complex bibliographic citations automatically.

**Example use case:**
```
Input text:
"Einstein A., Podolsky B., Rosen N. (1935). Can quantum-mechanical
description of physical reality be considered complete? Physical
Review 47: 777-780."

Output (parsed):
- Authors: Einstein A., Podolsky B., Rosen N.
- Year: 1935
- Title: Can quantum-mechanical description...
- Journal: Physical Review
- Volume: 47
- Pages: 777-780
```

**Algorithm configuration stored in database:**
```sql
algorithms (
    algorithm_name,    -- "Author extractor", "Year parser", etc.
    field_target,      -- Which field to populate
    delimiter_start,   -- Start pattern (e.g., "(")
    delimiter_end,     -- End pattern (e.g., ")")
    regex_pattern,     -- Optional regex for complex extraction
    trim_chars,        -- Characters to remove from result
    priority,          -- Order of execution (1-99)
    active             -- Enable/disable (0/1)
)
```

**25 algorithms configured for different patterns:**
- Author name extraction (multiple formats)
- Year detection (various citation styles)
- Title extraction
- Journal/magazine identification
- Volume/issue parsing
- Page number extraction
- Publisher detection

**Implementation:** [s/Algorithms/](s/Algorithms/) directory (9 files)

**Migration strategy:** Convert to Laravel Service classes with configurable pipelines.

---

### 4. Hierarchical Data with Unlimited Nesting 🌳

**The clever design:**
Uses **comma-separated value paths** to represent tree structures with unlimited depth, with persistent collapse/expand state per user.

**Data structure comparison:**
```sql
-- Traditional parent_id approach (limited depth):
id | parent_id | name
1  | NULL      | Root
2  | 1         | Child1
3  | 1         | Child2
4  | 2         | Grandchild

-- Legacy path-based approach (unlimited depth):
id | value_path              | name
1  | ""                      | Root
2  | "1"                     | Child1
3  | "1"                     | Child2
4  | "1,2"                   | Grandchild (under Child1)
5  | "1,2,4"                 | Great-grandchild
6  | "1,2,4,5"              | Great-great-grandchild
```

**Tree collapse state management:** ([s/Tree/Collapse.php:42-84](s/Tree/Collapse.php#L42-L84))
```php
function SetCollapse($dbh, $collapse_id, $collapse_set, $collapse_value, $settings_pad) {
    // Find all direct children using LIKE pattern matching
    $str_where = " WHERE ".$_SESSION['Catalog']['0']['value'].
                 " LIKE '".$collapse_set.",%' AND ".
                 "INSTR(SUBSTR(".$_SESSION['Catalog']['0']['value'].", ".
                 "LENGTH('".$collapse_set."') + 2), ',') = 0";

    if ($collapse_value == "-") {
        // Collapsing: store collapsed node in session
        $_SESSION['collapse'][$_SESSION['Catalog']['0']['table']][(string)$collapse_id] =
            array("set"=>$collapse_set, "count"=>GetCollapseCount($dbh, $collapse_set));
    } else {
        // Expanding: remove from session
        unset($_SESSION['collapse'][$_SESSION['Catalog']['0']['table']][(string)$collapse_id]);
    }
}
```

**Tree counting with hidden children:** ([s/Tree/Collapse.php:121-125](s/Tree/Collapse.php#L121-L125))
```php
function TreeCounts($total_count) {
    $arr_collapse = ArrayCollapse();
    // Subtract hidden children from total count
    return $total_count - array_sum($arr_collapse['n_count']);
}
```

**Complex SQL for hierarchical queries:** ([s/Utilities/DataBases.php:152-237](s/Utilities/DataBases.php#L152-L237))
```php
// VF = "Value Function" - generates complex CASE statements
// to handle comma-separated hierarchical data
function VF($max_lev, $tableSet, $idSet, $valueSet, $tableRef,
            $idRef, $valueRef, $main_table_ref, $default_valus, $alias_name) {
    // Generates dynamic SQL that extracts each level of hierarchy
    for ($i = 0; $i < $max_lev; $i++) {
        // Creates CASE statements with SUBSTRING_INDEX to parse CSV paths
        $req_arr[] = SetCaseText($tableRef, $idRef, $valueRef, ...);
    }
    return implode(", ", $req_arr);
}
```

**Migration strategy:** Replace with Laravel Nested Set package or Closure Table for better performance.

---

### 5. Complex Many-to-Many Relationships 🔗

**The problem:**
Author groups can have multiple authors. Publications can have multiple author groups. This creates **many-to-many-to-many** relationships.

**Current legacy approach (problematic):**
```sql
author_groups (
    id_author_group INT PRIMARY KEY,
    author_set VARCHAR(255)  -- Stores "10,11,12" as comma-separated author IDs
)

-- Query to extract 1st author from CSV:
SELECT SUBSTRING_INDEX(author_set, ',', 1) FROM author_groups;

-- Query for 2nd author (complex!):
SELECT SUBSTRING_INDEX(SUBSTRING_INDEX(author_set, ',', 2), ',', -1);
```

**Extraction implementation:** ([s/Utilities/DataBases.php:183-193](s/Utilities/DataBases.php#L183-L193))
```php
// Extract first author from comma-separated list
if ($i == 0) {
    $int_sel = "(SELECT ".$tableRef.".".$valueRef." FROM ".$tableRef.$to_from.
               " WHERE ".$to_where.$tableRef.".".$idRef." = ".
               "SUBSTRING_INDEX(".$tableSet.".".$valueSet.",',',1) LIMIT 1)";
} else {
    // For nth author, use nested SUBSTRING_INDEX
    $l_str = "SUBSTRING_INDEX(".$tableSet.".".$valueSet.",',',".(string)($i + 1).")";
    $res_str = "SUBSTRING_INDEX(".$l_str.",',',-1)";
    $prev_str = "SUBSTRING_INDEX(".$tableSet.".".$valueSet.",',',".(string)$i.")";
    $int_sel = "(SELECT ".$tableRef.".".$valueRef." FROM ".$tableRef.$to_from.
               " WHERE ".$to_where.$tableRef.".".$idRef." = ".$res_str.
               " AND ".$l_str." <> ".$prev_str." LIMIT 1)";
}
```

**Migration to proper pivot tables:**
```sql
-- New Laravel structure
author_group_author (
    id BIGINT PRIMARY KEY,
    author_group_id INT,
    author_id INT,
    position INT,  -- Order in group (1, 2, 3...)
    FOREIGN KEY (author_group_id) REFERENCES author_groups(id),
    FOREIGN KEY (author_id) REFERENCES authors(id)
)
```

---

### 6. Multi-Language System Architecture 🌍

**The design:**
All UI text stored in database with translations for 3 languages (English, Russian, Hebrew).

**Translation table structure:**
```sql
interface_texts (
    id_text INT PRIMARY KEY,        -- Text identifier (e.g., 156, 443)
    eng_text VARCHAR(500),          -- English translation
    rus_text VARCHAR(500),          -- Russian translation
    heb_text VARCHAR(500),          -- Hebrew translation
    context VARCHAR(100)            -- Where it's used
)
```

**Usage throughout codebase:**
```php
echo Title(156);  // "Database name required" in user's language
echo Title(1);    // "Connection error"
echo Title(443);  // Field validation message
echo Title(470);  // "Cannot save changes"
```

**Statistics:**
- **476 unique UI strings** × 3 languages = **1,429 translation records**
- Loaded once per session: [s/index.php:56-58](s/index.php#L56-L58)

**Language selection:** ([s/index.php:56-62](s/index.php#L56-L62))
```php
$_SESSION['common_langs'] = ReadLanguages($dbh, false);
$_SESSION['user_langs'] = SetLanguageList(2);
$_SESSION['titles'] = GetTitlesByLanguage($dbh, 1);  // Load English by default
$_SESSION['user_lang'] = array(1, "English");
```

**Migration strategy:** Convert to Laravel localization with JSON files:
```
resources/lang/
├── en.json  // {"156": "Database name required", ...}
├── ru.json  // {"156": "Требуется имя базы данных", ...}
└── he.json  // {"156": "נדרש שם מסד נתונים", ...}
```

---

### 7. Security Vulnerabilities ⚠️

**Critical issues found:**

**1. SQL Injection risks:** ([s/Utilities/DataBases.php:76-90](s/Utilities/DataBases.php#L76-L90))
```php
// Vulnerable: filter_where comes from user input, no prepared statements
$res = mysqli_query($dbh, "SELECT COUNT(*) FROM ".$_SESSION['Catalog'][$n]['table'].
                          " WHERE ".$_SESSION['catalog_param'][$n]['filter_where']);

// Also vulnerable: dynamic ORDER BY
$where_c = ($_SESSION['catalog_param'][$n]['filter_where'] == "") ?
           $_SESSION['catalog_param'][$n]['search_where'] :
           $_SESSION['catalog_param'][$n]['filter_where']." AND ".
           $_SESSION['catalog_param'][$n]['search_where'];
```

**2. Manual SQL escaping (fragile):** ([s/Utilities/Common.php:156-161](s/Utilities/Common.php#L156-L161))
```php
function VValue($s_str) {
    // Manual escaping - easy to miss
    $str = str_replace(chr(39), chr(39).chr(39), $s_str);  // ' becomes ''
    return str_replace(chr(92), chr(92).chr(92), $str);    // \ becomes \\
}
```

**3. XSS vulnerabilities:**
```php
echo $row[0];  // No htmlspecialchars() or escaping
echo $_POST['field_name'];  // Direct output
```

**4. No CSRF protection:**
```php
// Forms submit without tokens
<form method="post" action="...">
    <input name="field" value="...">
</form>
```

**5. Weak session security:** ([s/index.php:33](s/index.php#L33))
```php
session_start();  // No configuration, no regeneration on login
```

**6. Hardcoded credentials exposure:** ([s/index.php:35](s/index.php#L35))
```php
// Plain text file with MySQL passwords
$dbh = GetManagerDBFile("db_manager", $_SERVER['DOCUMENT_ROOT']."/s/_Credentials.txt");

// File format:
// Line 1: host
// Line 2: username
// Line 3: password (plain text!)
```

**Laravel security improvements:**
- ✅ Eloquent ORM prevents SQL injection
- ✅ Blade templates auto-escape ({{ }} prevents XSS)
- ✅ Built-in CSRF protection (@csrf directive)
- ✅ Secure session management with regeneration
- ✅ Environment variables for credentials (.env)
- ✅ Password hashing with bcrypt
- ✅ Rate limiting on routes
- ✅ Input validation and sanitization

---

### 8. String Validation and Character Encoding 🛡️

**System string validation:** ([s/Utilities/Common.php:138-144](s/Utilities/Common.php#L138-L144))
```php
function TestSysString($str) {
    $arr_str = str_split($str);
    // Must start with lowercase letter (a-z)
    if (ord($arr_str[0]) < 97 || ord($arr_str[0]) > 122) return false;
    // Rest must be: 0-9, underscore, or a-z
    for ($i = 1; $i < count($arr_str); $i++) {
        if (ord($arr_str[$i]) < 48 ||                          // Below '0'
            ord($arr_str[$i]) > 57 && ord($arr_str[$i]) < 95 ||  // Between '9' and '_'
            ord($arr_str[$i]) == 96 ||                           // Backtick
            ord($arr_str[$i]) > 122) return false;               // Above 'z'
    }
    return true;
}
```

**Character encoding management:**
- Multiple character sets supported (UTF-8, Windows-1251, etc.)
- Transliteration tables in database
- Per-database encoding configuration

**Implementation:** [s/Codings/](s/Codings/) directory

---

### 9. Soft Delete Pattern 🗑️

**Implementation:**
```sql
-- All main tables have soft delete flag
ALTER TABLE publications ADD COLUMN _del_mark TINYINT DEFAULT 0;
ALTER TABLE authors ADD COLUMN _del_mark TINYINT DEFAULT 0;

-- Queries always filter deleted records:
SELECT * FROM publications WHERE _del_mark = 0;

-- "Deletion" is just an update:
UPDATE publications SET _del_mark = 1 WHERE id = ?;
```

**Migration strategy:**
Keep `_del_mark` for compatibility, but add Laravel's soft deletes:
```php
// Laravel model
class Publication extends Model {
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    // Custom query scope to respect legacy flag
    protected static function boot() {
        parent::boot();
        static::addGlobalScope('notMarked', function ($query) {
            $query->where('_del_mark', 0);
        });
    }
}
```

---

### 10. Session-Heavy Architecture 💾

**Everything stored in $_SESSION:**

**Boot sequence loads massive session data:** ([s/index.php:39-72](s/index.php#L39-L72))
```php
$_SESSION['structure_errors'] = array();
$_SESSION['alarm'] = false;
$_SESSION['preliminary_flags'] = array("table_errors"=>array(), ...);
$_SESSION['mandatory_language_errors'] = array();
$_SESSION['scripts_title_ids'] = array();
$_SESSION['ex_title_ids'] = AllSpecialTitleNumbers($dbh);  // All text IDs
$_SESSION['common_langs'] = ReadLanguages($dbh, false);
$_SESSION['user_langs'] = SetLanguageList(2);
$_SESSION['titles'] = GetTitlesByLanguage($dbh, 1);  // 476 strings!
$_SESSION['arr_db'] = CreateDBArray($dbh);
$_SESSION['coding_list'] = SetCodingList($dbh);
$_SESSION['pre_ref'] = InvalidReferenceTable($dbh);
$_SESSION['Catalog'] = array(...);  // Catalog configuration
$_SESSION['catalog_param'] = array(...);  // Search/filter state
$_SESSION['collapse'] = array(...);  // Tree collapse state
// ... 30+ more session variables
```

**Problems:**
- **Large memory footprint** (can be 100s of KB per session)
- **No caching layer** (reloads on every request if session expires)
- **Session expires = lose all UI state**
- **Not scalable** to multiple servers (sticky sessions required)
- **Slow page loads** (serializing/deserializing huge arrays)

**Migration strategy:**
1. **Cache translations** - Load once, cache in Redis
2. **Database for state** - Store UI state in `user_preferences` table
3. **Lazy loading** - Load configurations only when needed
4. **Event-driven updates** - Livewire for reactive UI without full reloads

---

## 📈 System Statistics

**Code metrics:**
- Total files: 186 PHP files
- Modules: 32 directories
- Functions: ~500+ (estimated)
- Lines of code: ~20,000+ (estimated)

**Database metrics (from SQL dumps - test data):**
- Tables: 40+ across 4 databases
- Publications: 11 (test data - production may vary)
- Authors: 19 (test data - production may vary)
- Translations: 476 unique UI strings × 3 languages = 1,429 records
- Algorithms: 25 parsing algorithms

**Note:** Provided SQL dumps contain test/demo data. Actual production volume should be confirmed with system owner.

**Features:**
- Authentication ✓
- Multi-language ✓
- CRUD operations ✓
- Import/Export ✓
- Tree structures ✓
- Dynamic fields ✓
- Search & filter ✓
- Pagination ✓
- Soft deletes ✓
- Activity logging ✓
- Backup/restore ✓

---

## 🎯 Next Steps

### For Developers

1. Read [../docs/DEVELOPER_GUIDE_RU.md](../docs/DEVELOPER_GUIDE_RU.md)
2. Review [../docs/MIGRATION_PLAN.md](../docs/MIGRATION_PLAN.md)
3. Study legacy code in `/s/` directory
4. Begin Phase 1 implementation

### For Project Managers

1. Review [../docs/MIGRATION_STRATEGY_RU.md](../docs/MIGRATION_STRATEGY_RU.md)
2. Approve migration timeline
3. Allocate resources
4. Schedule kick-off meeting

### For Stakeholders

1. Read [../docs/MIGRATION_STRATEGY_RU.md](../docs/MIGRATION_STRATEGY_RU.md)
2. Understand risks and benefits
3. Approve budget and timeline
4. Designate project sponsor

---

## 📚 Additional Resources

**Documentation:**
- [../docs/MIGRATION_PLAN.md](../docs/MIGRATION_PLAN.md) - Technical migration plan
- [../docs/MIGRATION_STRATEGY_RU.md](../docs/MIGRATION_STRATEGY_RU.md) - Executive summary
- [../docs/DEVELOPER_GUIDE_RU.md](../docs/DEVELOPER_GUIDE_RU.md) - Developer guide
- [../docs/QUICKSTART.md](../docs/QUICKSTART.md) - Laravel quick start
- [../docs/LIVEWIRE_GUIDE.md](../docs/LIVEWIRE_GUIDE.md) - Livewire guide

**Legacy SQL Dumps:**
- `db_manager.sql` (2,080 lines) - System database
- `literature.sql` (845 lines) - Main database
- `phys_math_contents.sql` (147 lines) - Reference content
- `trees.sql` (224 lines) - Hierarchical data

**Legacy Code:**
- Entry point: `s/index.php` (89 lines)
- Utilities: `s/Utilities/` (11 files)
- Database: `s/DataBases/` (9 files)
- Catalogs: `s/Catalogs/` (18 files!)

---

## ⚠️ Important Notes

### DO NOT DELETE

This legacy system should **NOT** be deleted until:
- ✅ All functionality migrated
- ✅ All data migrated and validated
- ✅ New system tested in production
- ✅ Users trained
- ✅ 3-6 months of stable operation

### Keep for Reference

Even after successful migration, keep this code:
- Reference for business logic
- Documentation of requirements
- Historical record
- Backup fallback

### Rosenbrock Module

The `/Rosenbrock/` directory contains a **separate mathematical optimization module** and should be treated independently from the database management system.

---

## 🤝 Contributing

To contribute to the migration:

1. Read the migration plan
2. Choose a module to migrate
3. Create a feature branch
4. Implement according to Laravel conventions
5. Write tests
6. Submit pull request

---

## 📞 Support

**Questions about legacy system?**
- Review code in `/s/` directory
- Check SQL dumps for schema
- Examine `Utilities/` for core functions

**Questions about migration?**
- See [MIGRATION_PLAN.md](../docs/MIGRATION_PLAN.md)
- Consult [DEVELOPER_GUIDE_RU.md](../docs/DEVELOPER_GUIDE_RU.md)
- Create an issue in repository

---

**Last Updated:** 2025-10-11
**Status:** Documentation complete with detailed technical insights, migration pending
**Version:** Legacy system analysis v2.0
