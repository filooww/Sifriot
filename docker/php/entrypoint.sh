#!/bin/bash
set -e

echo "Starting container initialization..."

# Debug information
echo "Checking mounted volumes and permissions..."
ls -la /library || echo "Warning: Cannot access /library"
ls -la /var/www/html || echo "Warning: Cannot access /var/www/html"

# Check if running in Windows environment
if grep -q Microsoft /proc/version || grep -q WSL /proc/version; then
    echo "Running in Windows/WSL environment"
    WINDOWS_ENV=1
else
    echo "Running in Linux native environment"
    WINDOWS_ENV=0
fi

# Handle /library mount
if [ -d "/library" ]; then
    echo "Found /library directory"
    if [ "$WINDOWS_ENV" = "1" ]; then
        echo "Skipping permission changes on /library (Windows environment)"
    else
        echo "Attempting to set permissions on /library"
        chmod -R 755 /library 2>/dev/null || echo "Warning: Could not set permissions on /library"
    fi
else
    echo "Warning: /library directory not found"
fi

# Handle Laravel storage permissions
if [ -d "/var/www/html/storage" ]; then
    echo "Setting up Laravel storage permissions"
    find /var/www/html/storage -type d -exec chmod 755 {} \; 2>/dev/null || echo "Warning: Could not set directory permissions"
    find /var/www/html/storage -type f -exec chmod 644 {} \; 2>/dev/null || echo "Warning: Could not set file permissions"
    chown -R www-data:www-data /var/www/html/storage 2>/dev/null || echo "Warning: Could not change ownership of storage directory"
    
    # Ensure storage subdirectories exist
    mkdir -p /var/www/html/storage/framework/{sessions,views,cache}
    chmod -R 775 /var/www/html/storage/framework
fi

# Ensure proper Apache permissions
if [ -d "/var/www/html" ]; then
    echo "Setting up Apache permissions"
    chown -R www-data:www-data /var/www/html/public 2>/dev/null || echo "Warning: Could not set Apache permissions"
fi

echo "Container initialization completed"

# Run the main command
echo "Starting main process..."
exec "$@"