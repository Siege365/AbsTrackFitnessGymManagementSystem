# Use official PHP with Apache
FROM php:8.2-apache

# Install required extensions for Laravel
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev zip curl libzip-dev libsqlite3-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql pdo_sqlite zip

# Install Node.js 20 (needed to build Vite/Tailwind frontend assets)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Enable apache mod_rewrite (needed for Laravel)
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy app code
COPY . /var/www/html

# Set apache DocumentRoot to public and change listen port to 10000 for Render
RUN sed -i 's/80/10000/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Add Directory configuration to allow .htaccess overrides for Laravel routing
RUN echo '<Directory /var/www/html/public>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
    </Directory>' >> /etc/apache2/sites-available/000-default.conf

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Laravel PHP dependencies (production only)
RUN composer install --no-dev --optimize-autoloader

# Install npm dependencies and build Vite assets (Tailwind CSS + JS)
RUN npm install && npm run build

# Copy .env.example to .env if no .env is present
# (APP_KEY will be generated at startup if not set via Render env vars)
RUN cp -n .env.example .env

# Create SQLite database file if using SQLite (fallback)
RUN mkdir -p /var/www/html/database \
    && touch /var/www/html/database/database.sqlite

# Create storage symlink
RUN php artisan storage:link --force --env=production 2>/dev/null || php artisan storage:link --force

# Create uploads directory and set permissions
RUN mkdir -p /var/www/html/storage/app/public/uploads \
    && chown -R www-data:www-data /var/www/html/storage/app/public/uploads \
    && chmod -R 775 /var/www/html/storage/app/public/uploads

# Set permissions for storage, database, and bootstrap cache
RUN chown -R www-data:www-data \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache \
    /var/www/html/database \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose Render required port
EXPOSE 10000

# Copy startup script and make it executable
COPY start.sh /start.sh
RUN chmod +x /start.sh

# Run migrations and start Apache
CMD ["/start.sh"]
