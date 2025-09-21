FROM php:8.4-fpm-alpine

ARG VERSION=latest
ENV APP_VERSION=${VERSION}

LABEL org.opencontainers.image.title="Fin-Tracker" \
    org.opencontainers.image.description="Household Finance Manager" \
    org.opencontainers.image.url="https://github.com/barthjs/fin-tracker" \
    org.opencontainers.image.source="https://github.com/barthjs/fin-tracker" \
    org.opencontainers.image.version=${VERSION} \
    org.opencontainers.image.licenses="MIT"

# Create application user
RUN addgroup -g 1000 application && \
    adduser -D -u 1000 -G application -h /home/application -s /bin/sh application

# Install base packages
RUN apk add --no-cache \
        curl \
        nginx \
        supervisor && \
    # Clear all php-fpm default configurations \
    rm -rf /usr/local/etc/php-fpm.d/*.conf

# Install php extensions
COPY --from=mlocati/php-extension-installer:latest /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions \
        intl \
        opcache \
        pdo_mysql \
        pdo_pgsql \
        zip && \
        rm -f /usr/local/bin/install-php-extensions

# Copy php configuration files
RUN mv /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini
COPY docker/php/php.ini /usr/local/etc/php/conf.d/php.ini
COPY docker/php/php-prod.ini /usr/local/etc/php/conf.d/php-prod.ini
COPY docker/php/application.conf /usr/local/etc/php-fpm.d/application.conf

# Copy nginx configuration files
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/prod.conf /etc/nginx/conf.d/prod.conf

# Copy Supervisor configuration files
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/supervisor.d /etc/supervisor.d

# App setup
WORKDIR /app
COPY --chown=application:application . /app

# Install app dependencies and build frontend
RUN apk add --no-cache --virtual .build-deps nodejs npm && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader && \
    npm install &&  \
    npm run build && \
    php artisan storage:link && \
    # Remove build dependencies \
    apk del .build-deps &&  \
    rm -rf /usr/local/bin/composer \
    docker node_modules resources/css composer.lock package*.json *.js

# Entrypoint
COPY docker/start.sh /start.sh

EXPOSE 80
ENTRYPOINT ["/start.sh"]

HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -fsS http://127.0.0.1:80/up || exit 1
