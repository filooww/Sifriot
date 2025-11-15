#!/bin/bash
set -e

# Fix permissions for mounted library paths (skip on Windows Docker mounts)
if [ -d "/library" ]; then
    # Only try to change permissions on Linux-native paths, not Windows mounts
    # Windows mounts don't support chown/chmod and will hang/fail
    if ! stat -c %a /library 2>/dev/null | grep -q .; then
        # If stat fails, it's likely a Windows mount, skip
        echo "Skipping permission changes on /library (Windows mount detected)"
    else
        chmod -R 755 /library 2>/dev/null || true
    fi
fi

# Fix permissions for Laravel storage (this is Linux, not mounted from Windows)
if [ -d "/var/www/html/storage" ]; then
    chown -R www-data:www-data /var/www/html/storage 2>/dev/null || true
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