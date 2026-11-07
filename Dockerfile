# OpenBKA production image. PHP-FPM + nginx, from serversideup/php.
FROM serversideup/php:8.4-fpm-nginx AS base

USER root
RUN install-php-extensions intl bcmath

FROM base AS vendor
WORKDIR /var/www/html
COPY --chown=www-data:www-data composer.json composer.lock ./
USER www-data
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

FROM base
WORKDIR /var/www/html

ENV PHP_OPCACHE_ENABLE=1 \
    AUTORUN_ENABLED=true \
    AUTORUN_LARAVEL_MIGRATION_ISOLATION=true \
    SSL_MODE=off

COPY --chown=www-data:www-data --from=vendor /var/www/html/vendor ./vendor
COPY --chown=www-data:www-data . .

USER www-data
RUN composer dump-autoload --optimize --no-dev \
    && php artisan filament:assets \
    && rm -f .env
