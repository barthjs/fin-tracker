# syntax=docker/dockerfile:1.7-labs
FROM php:8.5-cli-alpine AS composer-builder
WORKDIR /app

COPY --from=mlocati/php-extension-installer:latest /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions \
        intl \
        zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY composer.* ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

FROM node:24-alpine AS node-builder
WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY --from=composer-builder /app/vendor ./vendor
COPY --parents \
    public \
    resources \
    vite.config.js \
    ./
RUN npm run build

FROM php:8.5-fpm-alpine
WORKDIR /app

ARG VERSION=latest
ENV APP_VERSION=${VERSION}

RUN addgroup -g 1000 application && \
    adduser -D -u 1000 -G application -h /home/application -s /bin/sh application && \
    apk add --no-cache \
        curl \
        nginx \
        supervisor

COPY --from=mlocati/php-extension-installer:latest /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions \
        intl \
        opcache \
        pdo_mysql \
        pdo_pgsql \
        zip && \
    rm /usr/local/bin/install-php-extensions && \
    rm -rf /usr/local/etc/php-fpm.d/*.conf && \
    mv /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini

COPY docker/php/php.ini docker/php/php-prod.ini /usr/local/etc/php/conf.d/
COPY docker/php/application.conf /usr/local/etc/php-fpm.d/application.conf

COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/prod.conf /etc/nginx/conf.d/prod.conf

COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/supervisor.d /etc/supervisor.d

COPY docker/start.sh /start.sh
COPY docker/entrypoint.d/*.sh /entrypoint.d/

COPY --chown=application:application --parents \
    app \
    bootstrap \
    config \
    database \
    lang \
    resources/icons \
    resources/views \
    routes \
    storage \
    artisan \
    composer.json \
    ./
COPY --chown=application:application --from=composer-builder /app/vendor ./vendor
COPY --chown=application:application --from=node-builder /app/public ./public

RUN php artisan storage:link

EXPOSE 80
ENTRYPOINT ["/start.sh"]

HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -fsS http://127.0.0.1:80/up || exit 1
