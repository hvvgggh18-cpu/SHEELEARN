FROM php:8.3-fpm

ENV DEBIAN_FRONTEND=noninteractive
ENV LANG=C.UTF-8
ENV LANGUAGE=C.UTF-8
ENV LC_ALL=C.UTF-8
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_HOME=/tmp/composer
ENV COMPOSER_MEMORY_LIMIT=-1

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends apt-utils; \
    apt-get install -y --no-install-recommends \
        ca-certificates apt-transport-https gnupg2 lsb-release \
        locales locales-all \
        build-essential pkg-config \
        git unzip zip curl \
        libpng-dev \
        libpng-tools \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libzip-dev zlib1g-dev \
        libonig-dev libxml2-dev \
        libgcrypt20 libgpg-error-dev libgpm2 \
        nginx supervisor \
    ; \
    docker-php-ext-configure gd --with-jpeg --with-freetype; \
    docker-php-ext-install -j"$(nproc)" pdo pdo_mysql zip gd bcmath mbstring pcntl; \
    # remove build deps to slim image
    apt-get purge -y --auto-remove build-essential pkg-config; \
    apt-get clean; \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy composer files first to leverage Docker layer cache
COPY composer.json composer.lock ./
# Install PHP dependencies but do not run Composer scripts during image build
RUN composer --version && composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts --classmap-authoritative || true

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