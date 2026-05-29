#!/usr/bin/env sh
set -e

mkdir -p \
    storage/api-docs \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

chown -R laravel:laravel storage bootstrap/cache

if [ "$1" = "php-fpm" ]; then
    if [ -f artisan ] && [ -d vendor ]; then
        su-exec laravel php artisan l5-swagger:generate
    fi

    exec "$@"
fi

exec su-exec laravel "$@"
