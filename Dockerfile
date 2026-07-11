# syntax=docker/dockerfile:1

FROM node:22-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY resources ./resources
COPY public ./public
COPY vite.config.js tailwind.config.js postcss.config.js jsconfig.json ./
RUN npm run build

FROM dunglas/frankenphp:1-php8.4-alpine AS app

WORKDIR /app

ENV SERVER_NAME=:8080 \
    SERVER_ROOT=public \
    PHP_OPCACHE_ENABLE=1 \
    COMPOSER_ALLOW_SUPERUSER=1

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && install-php-extensions \
        bcmath \
        exif \
        gd \
        intl \
        opcache \
        pcntl \
        pdo_mysql \
        zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-interaction \
        --prefer-dist \
        --optimize-autoloader \
        --no-scripts

COPY . .
COPY --from=frontend /app/public/build ./public/build
COPY docker/entrypoint.sh /usr/local/bin/erp-entrypoint

RUN composer dump-autoload --optimize \
    && mkdir -p storage/app/private/signatures storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chmod +x /usr/local/bin/erp-entrypoint \
    && chown -R www-data:www-data storage bootstrap/cache public /data /config

USER www-data

EXPOSE 8080

ENTRYPOINT ["erp-entrypoint"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
