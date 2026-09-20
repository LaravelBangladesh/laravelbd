# syntax=docker/dockerfile:1.7

FROM php:8.5-cli-trixie AS wayfinder

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN apt-get update && apt-get install -y --no-install-recommends git unzip \
    && install-php-extensions intl zip bcmath \
    && apt-get purge -y --auto-remove \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader --no-scripts

COPY . .
RUN php artisan wayfinder:generate --with-form

FROM node:22-alpine AS build

WORKDIR /app

COPY package.json package-lock.json* ./
RUN if [ -f package-lock.json ]; then npm ci; else npm install; fi

COPY . .
COPY --from=wayfinder /var/www/html/resources/js/actions ./resources/js/actions
COPY --from=wayfinder /var/www/html/resources/js/routes ./resources/js/routes
COPY --from=wayfinder /var/www/html/resources/js/wayfinder ./resources/js/wayfinder
ENV WAYFINDER_COMMAND=true
RUN npm run build:ssr

FROM node:22-alpine AS runtime

WORKDIR /app

COPY package.json package-lock.json* ./
RUN if [ -f package-lock.json ]; then npm ci --omit=dev; else npm install --omit=dev; fi

COPY --from=build /app/bootstrap/ssr ./bootstrap/ssr

EXPOSE 13714

CMD ["node", "bootstrap/ssr/ssr.js"]
