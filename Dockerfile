# syntax=docker/dockerfile:1.7-labs
FROM php:8.5-fpm-alpine as base
WORKDIR /app

ENV PUID=1000
ENV PGID=1000
ARG S6_OVERLAY_VERSION=3.2.0.2
ENV S6_VERBOSITY=1
ENV USER=application

COPY --from=mlocati/php-extension-installer:latest /usr/bin/install-php-extensions /usr/local/bin/
RUN set -eux \
    && addgroup -g ${PUID} ${USER} \
    && adduser -D -u ${PGID} -G ${USER} -h /home/${USER} -s /bin/zsh ${USER} \
    && apk add --no-cache \
    nginx \
    shadow \
    && wget -O /tmp/s6-overlay-noarch.tar.xz https://github.com/just-containers/s6-overlay/releases/download/v${S6_OVERLAY_VERSION}/s6-overlay-noarch.tar.xz \
    && wget -O /tmp/s6-overlay-x86_64.tar.xz https://github.com/just-containers/s6-overlay/releases/download/v${S6_OVERLAY_VERSION}/s6-overlay-x86_64.tar.xz \
    && tar -C / -Jxpf /tmp/s6-overlay-noarch.tar.xz \
    && tar -C / -Jxpf /tmp/s6-overlay-x86_64.tar.xz \
    && rm -f /tmp/s6-overlay-*.tar.xz \
    && install-php-extensions \
    intl \
    opcache \
    pdo_mysql \
    pdo_pgsql \
    zip \
    && rm -f /usr/local/bin/install-php-extensions \
    && rm -rf /usr/local/etc/php-fpm.d/*.conf

FROM base AS dev

ENV PHP_IDE_CONFIG="serverName=fin-tracker"

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY --from=mlocati/php-extension-installer:latest /usr/bin/install-php-extensions /usr/local/bin/
RUN set -eux \
    && apk add --no-cache \
    bash \
    curl \
    git \
    nodejs \
    npm \
    zsh \
    zsh-vcs \
    shadow \
    && install-php-extensions \
    xdebug \
    && rm -f /usr/local/bin/install-php-extensions \
    && mv $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini

COPY docker/php/php.ini docker/php/php-dev.ini $PHP_INI_DIR/conf.d/
COPY docker/php/application.conf /usr/local/etc/php-fpm.d/application.conf

COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/nginx-dev.conf /etc/nginx/conf.d/dev.conf

COPY docker/s6-overlay/base /etc/s6-overlay
COPY docker/s6-overlay/dev /etc/s6-overlay

USER ${USER}
RUN sh -c "$(curl -fsSL https://raw.githubusercontent.com/ohmyzsh/ohmyzsh/master/tools/install.sh)" "" --unattended
COPY docker/.zshrc /home/${USER}/.zshrc
USER root

EXPOSE 8080

ENTRYPOINT ["/init"]

FROM base AS composer-builder

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY composer.* ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts --no-progress

FROM node:24-alpine AS node-builder
WORKDIR /app

COPY package*.json ./
RUN npm ci --omit=dev

COPY --from=composer-builder /app/vendor ./vendor
COPY --parents \
    public \
    resources \
    vite.config.js \
    ./
RUN npm run build

FROM base as prod

ARG VERSION=latest
ENV APP_VERSION=${VERSION}

RUN mv $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini

COPY docker/php/php.ini docker/php/php-prod.ini $PHP_INI_DIR/conf.d/
COPY docker/php/application.conf /usr/local/etc/php-fpm.d/application.conf

COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/nginx-prod.conf /etc/nginx/conf.d/prod.conf

COPY docker/s6-overlay/base /etc/s6-overlay
COPY docker/s6-overlay/prod /etc/s6-overlay

COPY --parents \
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
COPY --from=composer-builder /app/vendor ./vendor
COPY --from=node-builder /app/public ./public

RUN php artisan storage:link

EXPOSE 8080

ENTRYPOINT ["/init"]

HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD wget -q --spider http://localhost:8080/api/up || exit 1
