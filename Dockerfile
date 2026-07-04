# ===== Stage 1: Composer dependencies =====
FROM composer:2 AS vendor

WORKDIR /app

# Copy composer files
COPY composer.json composer.lock ./

# Install production dependencies (no dev, no scripts — handled in stage 3)
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --no-interaction

# Copy rest of source for autoloader optimization
COPY . .

# Generate optimized autoloader now that source is in place
RUN composer dump-autoloader --optimize --classmap-authoritative --no-dev

# ===== Stage 2: Node.js frontend build =====
FROM node:20-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

COPY . .
RUN npm run build

# ===== Stage 3: Runtime (PHP 8.2 + Apache + mod_php) =====
FROM php:8.2-apache-bookworm AS runtime

LABEL maintainer="tariq.alwalidi@gmail.com" \
      project="shaleh-alsarat" \
      description="Single-chalet management MVP — Laravel 11 + Inertia + React"

# Install system dependencies needed by PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libwebp-dev \
        libzip-dev \
        libicu-dev \
        libonig-dev \
        mariadb-client \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions required by Laravel + the app
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        intl \
        mbstring \
        pdo_mysql \
        zip \
        gd \
        exif \
        pcntl \
        opcache

# Install opcache configuration for production
RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.enable_cli=0'; \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.interned_strings_buffer=8'; \
        echo 'opcache.max_accelerated_files=10000'; \
        echo 'opcache.validate_timestamps=0'; \
        echo 'opcache.revalidate_freq=2'; \
        echo 'opcache.fast_shutdown=1'; \
        echo 'opcache.save_comments=1'; \
        echo 'opcache.jit=tracing'; \
    } > /usr/local/etc/php/conf.d/opcache.ini

# Apache modules
RUN a2enmod rewrite headers

# Production php.ini
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Install Composer for runtime use (we already vendor-copied in stage 1)
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Copy the Laravel app
COPY --from=vendor /app /var/www/html

# Copy built frontend assets (overwrites /public/build)
COPY --from=frontend /app/public/build /var/www/html/public/build

# Copy our Apache config and entrypoint script
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Set working directory and ownership
WORKDIR /var/www/html
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \;

# Allow www-data to write to storage + cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Image size optimization: clean up dev files that snuck in
RUN rm -rf /var/www/html/tests /var/www/html/.git* /var/www/html/.dockerignore \
    && rm -rf /var/www/html/node_modules /var/www/html/.phpunit* \
    && find /var/www/html -name '.gitkeep' -delete 2>/dev/null || true

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=3 \
    CMD curl -fsS http://localhost/up || exit 1

ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]
