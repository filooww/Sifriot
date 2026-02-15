<p align="center"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></p>

# sifriot - Library Management System

**sifriot** is a modern, multilingual Library Management System built with **Laravel 12** and **Livewire 3**. It is designed for managing extensive digital catalogs, supporting bulk file operations, advanced metadata extraction, and rich user engagement features.

## 🚀 Key Features

-   **Catalog-First Architecture**: Manage large file repositories (1TB+) directly from the disk.
-   **Multilingual Support**: Full support for **English**, **Russian**, and **Hebrew** (RTL).
-   **Modern UI**: Built with **Tailwind CSS** and **Alpine.js** for a responsive, interactive experience.
-   **User Engagement**: Authenticated users can View, Like, Comment, and Bookmark publications.
-   **Role-Based Access**: Granular permissions for Guests, Users, and Administrators.
-   **Bulk Operations**: Fast folder scanning and automatic metadata extraction (PDF, DOCX).

## 🛠 Technology Stack

-   **Backend**: PHP 8.4, Laravel 12
-   **Frontend**: Livewire 3, Tailwind CSS, Alpine.js
-   **Database**: MySQL 8.0
-   **Search**: MySQL FULLTEXT Indexes
-   **Dev Environment**: Laragon (Windows) / Docker (Linux/Mac)

## 🏁 Getting Started (Windows/Laragon)

We recommend using **Laragon** for local development on Windows 11.

👉 **[Read the Quickstart Guide](docs/QUICKSTART.md)**

### Prerequisites
-   PHP 8.4+
-   MySQL 8.0+
-   Composer
-   Node.js & NPM

## 📚 Documentation

-   **[Architecture Overview](docs/architecture.md)**: System design and component details.
-   **[Developer Guide (RU)](docs/DEVELOPER_GUIDE_RU.md)**: Comprehensive guide for Russian-speaking developers.
-   **[Livewire Guide](docs/LIVEWIRE_GUIDE.md)**: Best practices for Livewire components.

## 🧪 Testing

Run the test suite to ensure everything is working:

```bash
php artisan test
```

## 📄 License

This software is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
