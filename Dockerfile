FROM php:8.3-fpm

# Avoid interactive prompts during package installation
ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates apt-transport-https gnupg2 lsb-release \
        build-essential pkg-config \
        git unzip zip curl \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libzip-dev zlib1g-dev \
        libonig-dev libxml2-dev \
        libgcrypt20 libgpg-error-dev libgpm2 \
        nginx supervisor \
    && docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install -j"$(nproc)" pdo pdo_mysql zip gd bcmath mbstring pcntl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy composer files first to leverage Docker layer cache
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Copy application files
COPY . .

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www \
    && chmod -R 775 storage bootstrap/cache

# Configure php-fpm to listen on TCP for nginx
RUN sed -i "s/listen = .*/listen = 127.0.0.1:9000/" /usr/local/etc/php-fpm.d/www.conf

# Put nginx config in place
COPY nginx.conf /etc/nginx/sites-enabled/default

# Supervisor configuration will run both php-fpm and nginx
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 10000

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]