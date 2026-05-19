FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    $PHPIZE_DEPS \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libpq-dev \
    oniguruma-dev \
    libxml2-dev \
    nodejs \
    npm \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd \
    && pecl install redis \
    && docker-php-ext-enable redis

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
