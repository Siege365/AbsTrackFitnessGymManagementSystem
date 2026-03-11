#!/bin/bash
set -e

# If no APP_KEY is set (or it's empty), generate one
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# Run database migrations (creates sessions, cache, jobs, and all app tables)
php artisan migrate --force

# Cache config and routes for performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start Apache
exec apache2-foreground
