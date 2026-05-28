FROM php:8.4-fpm-alpine

WORKDIR /var/www/html

RUN apk add --no-cache \
    bash \
    git \
    icu-dev \
    libzip-dev \
    postgresql-dev \
    unzip \
    zip \
    && docker-php-ext-install \
    bcmath \
    intl \
    pdo_pgsql \
    pgsql \
    zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/php/conf.d/99-performance.ini /usr/local/etc/php/conf.d/99-performance.ini

RUN addgroup -g 1000 laravel \
    && adduser -G laravel -u 1000 -D laravel

CMD ["php-fpm"]
