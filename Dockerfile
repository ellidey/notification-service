FROM php:8.4-fpm-alpine

WORKDIR /var/www/html

RUN apk add --no-cache \
    bash \
    freetype-dev \
    git \
    icu-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libzip-dev \
    postgresql-dev \
    su-exec \
    unzip \
    zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    bcmath \
    gd \
    intl \
    pdo_pgsql \
    pgsql \
    zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/php/conf.d/99-performance.ini /usr/local/etc/php/conf.d/99-performance.ini
COPY docker/php/php-fpm.d/zz-laravel.conf /usr/local/etc/php-fpm.d/zz-laravel.conf
COPY docker/php/entrypoint.sh /usr/local/bin/laravel-entrypoint

RUN addgroup -g 1000 laravel \
    && adduser -G laravel -u 1000 -D laravel \
    && chmod +x /usr/local/bin/laravel-entrypoint

ENTRYPOINT ["laravel-entrypoint"]
CMD ["php-fpm"]
