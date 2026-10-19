FROM php:8.4-fpm-alpine

WORKDIR /var/www/html

RUN apk add --no-cache --virtual .build-deps \
        freetype-dev \
        icu-dev \
        libjpeg-turbo-dev \
        libpng-dev \
        libzip-dev \
    && apk add --no-cache \
        freetype \
        git \
        icu-libs \
        libjpeg-turbo \
        libpng \
        libzip \
        mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j "$(nproc)" \
        bcmath \
        gd \
        intl \
        pcntl \
        pdo_mysql \
        zip \
    && apk del -f .build-deps

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Code is bind-mounted by docker-compose.yml, so nothing is COPYed here.
