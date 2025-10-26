#!/bin/bash
set -e

# Fix permissions for mounted library paths
if [ -d "/library" ]; then
    # Make sure www-data can read/write the library folder
    chmod -R 755 /library 2>/dev/null || true
    chown -R www-data:www-data /var/www/html/storage 2>/dev/null || true
fi

# Run the main command
exec "$@"
