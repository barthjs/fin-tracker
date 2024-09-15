# Apache, Composer, PHP
FROM webdevops/php-nginx:8.3-alpine

# Labels
LABEL vendor="barthjs"
LABEL maintainer="barthjs"
LABEL org.opencontainers.image.url="https://github.com/barthjs/fin-tracker"
LABEL org.opencontainers.image.source="https://github.com/barthjs/fin-tracker"
LABEL org.opencontainers.image.licenses="MIT"
LABEL org.opencontainers.image.title="Fin-Tracker"

ENV WEB_DOCUMENT_ROOT=/app/public

# Node.js, NPM
RUN apk add --no-cache nodejs npm
RUN npm install npm@latest -g

# App installation
WORKDIR /app
COPY . .
RUN chown -R application:application . \
    && composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader \
    && npm install \
    && npm run build

# Optimization
RUN php artisan storage:link \
    && php artisan filament:optimize \
    && php artisan filament:cache-components \
    && php artisan icons:cache \
    && chown -R application:application storage

COPY .docker/worker.conf /opt/docker/etc/supervisor.d/worker.conf
COPY .docker/artisan.sh /opt/docker/provision/entrypoint.d/artisan.sh

# Cleanup
RUN rm -rf .env .git tests \
    && apk del nodejs npm
