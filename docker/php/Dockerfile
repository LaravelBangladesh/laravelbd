# syntax=docker/dockerfile:1.7

FROM php:8.5-fpm-trixie AS base

ARG UID=1000
ARG GID=1000

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        curl \
    && install-php-extensions \
        pdo_pgsql \
        pgsql \
        intl \
        zip \
        bcmath \
        opcache \
        pcntl \
        gd \
        exif \
        redis \
    && apt-get purge -y --auto-remove \
    && rm -rf /var/lib/apt/lists/*

ENV COMPOSER_HOME=/var/www/.composer

RUN groupmod -o -g "${GID}" www-data \
    && usermod -o -u "${UID}" -g www-data www-data \
    && mkdir -p /var/www/html /var/www/.composer \
    && chown -R www-data:www-data /var/www/html /var/www/.composer

WORKDIR /var/www/html

FROM base AS development

RUN install-php-extensions pcov

COPY docker/php/php.dev.ini /usr/local/etc/php/conf.d/zz-app.ini

USER www-data

EXPOSE 9000

CMD ["php-fpm"]

FROM base AS wayfinder

COPY --chown=www-data:www-data composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader --no-scripts

COPY --chown=www-data:www-data . .
RUN php artisan wayfinder:generate --with-form

FROM node:22-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json* ./
RUN if [ -f package-lock.json ]; then npm ci; else npm install; fi

COPY . .
COPY --from=wayfinder /var/www/html/resources/js/actions ./resources/js/actions
COPY --from=wayfinder /var/www/html/resources/js/routes ./resources/js/routes
COPY --from=wayfinder /var/www/html/resources/js/wayfinder ./resources/js/wayfinder
ENV WAYFINDER_COMMAND=true
RUN npm run build:ssr

FROM base AS production

COPY docker/php/php.prod.ini /usr/local/etc/php/conf.d/zz-app.ini

COPY --chown=www-data:www-data composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader --no-scripts

COPY --chown=www-data:www-data . .
COPY --from=frontend --chown=www-data:www-data /app/public/build ./public/build
COPY --from=frontend --chown=www-data:www-data /app/bootstrap/ssr ./bootstrap/ssr

COPY --chmod=0755 docker/php/entrypoint.sh /usr/local/bin/entrypoint

RUN composer dump-autoload --optimize --no-dev \
    && php artisan package:discover --ansi \
    && chown -R www-data:www-data storage bootstrap/cache

USER www-data

EXPOSE 9000

ENTRYPOINT ["entrypoint"]

CMD ["php-fpm"]
