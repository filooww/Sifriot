# Quickstart Guide (Laragon on Windows 11)

This guide outlines the steps to set up the **sifriot** library management system on a Windows 11 environment using **Laragon**.

## Prerequisites

1.  **Laragon**: Download and install the "Full" edition from [laragon.org](https://laragon.org/download/).
    -   Ensure it includes **PHP 8.4+**, **Apache/Nginx**, **MySQL 8.0+**.
    -   *Note: You may need to update PHP manually in Laragon/bin if the installer comes with an older version.*
2.  **Composer**: Installed globally or accessible via Laragon's terminal.
3.  **Node.js & NPM**: Required for building frontend assets (Vite).
4.  **Git**: For cloning the repository.

## Installation Steps

### 1. Clone the Repository

Open Laragon's **Terminal** (Cmder) and navigate to the `www` directory:

```bash
cd C:\laragon\www
git clone <repository-url> sifriot
cd sifriot
```

*Note: Laragon automatically creates a hostname based on the folder name (e.g., `http://sifriot.test`).*

### 2. Configure Environment

Copy the example environment file:

```bash
cp .env.example .env
```

Open `.env` and configure your database settings:

```dotenv
APP_URL=http://sifriot.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sifriot
DB_USERNAME=root
DB_PASSWORD=
```

*Note: Default Laragon MySQL `root` user has an empty password.*

### 3. Install Dependencies

Install PHP and JavaScript dependencies:

```bash
composer install
npm install
```

### 4. Application Setup

Generate the application key and create the storage link:

```bash
php artisan key:generate
php artisan storage:link
```

### 5. Database Setup

Create the database (if not already created) and run migrations:

```bash
# You can create the DB via HeidiSQL or command line:
mysql -u root -e "CREATE DATABASE IF NOT EXISTS sifriot;"

# Run migrations and seeders
php artisan migrate --seed
```

### 6. Build Frontend

Build the frontend assets:

```bash
npm run build
```

## Running the Application

1.  Start **Laragon** and click **Start All**.
2.  Open your browser and visit: **[http://sifriot.test](http://sifriot.test)**

## Development Commands

-   **Watch Frontend Changes**: `npm run dev` (for hot-module replacement)
-   **Run Tests**: `php artisan test`
-   **Queue Worker**: Not required in development (defaults to `sync`). For production, run `php artisan queue:work`.
-   **Clear Cache**: `php artisan optimize:clear`

## Troubleshooting

-   **Virtual Host Not Working?**: Check if Laragon has updated `C:\Windows\System32\drivers\etc\hosts`. You may need to run Laragon as Administrator.
-   **PHP Version Issues?**: Ensure Laragon is pointing to PHP 8.4+ (**Menu > PHP > Version**).
