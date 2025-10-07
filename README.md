# Literature Database Manager

A PHP-based web application for managing literature collections with hierarchical categorization, file attachments, and flexible field configurations.

## Overview

This project is a custom content management system designed for organizing academic and literary publications with support for:
- Multi-author publications with author grouping
- Hierarchical theme and series categorization
- File attachments (PDFs, documents)
- User session management
- Dynamic field configuration system
- Multiple database schemas for different content types

## Project Structure

```
HTDocs/                     # Web application root
├── index.php              # Entry point
├── phpMyAdmin/            # Database administration interface
├── Images/                # Image assets
├── Rosenbrock/            # Application module
├── s/                     # Application module
├── *.sql                  # Database schemas
│
MySQL_DB/                  # MySQL database files (excluded from git)
├── Data/                  # Database data directory (gitignored)
│
Apache/                    # Apache server (excluded from git)
MySQL/                     # MySQL binaries (excluded from git)
PHP/                       # PHP installation (excluded from git)
SessionPath/               # PHP session files (excluded from git)
```

## Database Schemas

### 1. Literature Database (`literature.sql`)
Main publication management system featuring:

**Core Tables:**
- `publication` - Publications (books, articles, magazines)
- `files` - File attachments per publication
- `authors` / `author_groups` - Author management with grouping support
- `themes` / `theme_sets` - Theme categorization
- `parts` / `part_sets` - Series and hierarchical organization
- `publishings` - Publisher information
- `magazines` - Magazine titles
- `issue_types` - Publication type taxonomy

**User Management:**
- `userlist` - User accounts and preferences
- `user_settings` - Per-user configuration
- `collapse_ids` - UI state persistence

**Metadata & Configuration:**
- `field_config` / `field_config_2` - Dynamic form field definitions
- `table_definitions` - Table metadata and behavior
- `db_configs` - Database-level configuration

### 2. Physics/Math Contents (`phys_math_contents.sql`)
Specialized system for hierarchical content navigation:
- `contents` - Tree-structured content with references and Russian names
- Supports expandable/collapsible tree views
- File references and abbreviations

### 3. Trees Database (`trees.sql`)
Generic hierarchical data management:
- `parts` / `part_tree` - Parent-child tree relationships
- `userlist` - Simple authentication (username/password)
- Configurable table definitions

## Features

### Dynamic Field Configuration
The system includes a sophisticated field configuration engine (`field_config`, `field_config_2`) that allows:
- Custom field types (string, integer, date, select, URL, reference)
- Field validation and constraints
- Search and filter modes
- Sorting and display ordering
- UI layout control (alignment, width percentages)
- Case-sensitive/insensitive matching

### Hierarchical Organization
Multiple levels of categorization:
- **Series/Parts**: Nested hierarchies (e.g., Literature → 19th Century → Tolstoy)
- **Themes**: Multi-theme tagging with theme sets
- **Author Groups**: Support for multi-author works

### File Management
- Multiple files per publication
- File metadata (description, issue year, volume, number, page)
- File size tracking
- Source attribution

## Technology Stack

- **Backend**: PHP 5.5.14 (legacy version)
- **Database**: MySQL 5.6.19
- **Web Server**: Apache
- **Admin Tool**: phpMyAdmin 4.7.1
- **Character Encoding**: UTF-8 with utf8_bin collation (supports Cyrillic)

## Development Notes

### Repository Cleanup
This repository previously included the entire WAMP/LAMP stack (~780MB). The `.gitignore` has been updated to exclude:
- Server binaries (Apache, MySQL, PHP)
- Runtime session files
- Database data directories
- OS and IDE-specific files

Only application code and schemas are now tracked.

### Database Setup
1. Import the SQL schemas from `HTDocs/*.sql`
2. Configure database connection settings
3. Ensure MySQL data directory is created at `MySQL_DB/Data/`

### Character Support
- Full Unicode support (UTF-8)
- Extensive Cyrillic (Russian) text support
- Case-insensitive search with `*_low` fields (lowercased copies)

## Legacy Considerations

This is a legacy application running on:
- PHP 5.5 (EOL 2016)
- MySQL 5.6 (EOL 2021)

For production use, consider upgrading to:
- PHP 8.x
- MySQL 8.x or MariaDB
- Modern framework (Laravel, Symfony)

## License

License information not specified in the repository.
