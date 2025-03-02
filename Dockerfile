# Base image for PHP and NGINX
FROM webdevops/php-nginx:8.3-alpine

ARG VERSION=dev
ENV APP_VERSION=${VERSION} \
    WEB_DOCUMENT_ROOT=/app/public

# Labels
LABEL org.opencontainers.image.url="https://github.com/barthjs/fin-tracker" \
      org.opencontainers.image.source="https://github.com/barthjs/fin-tracker" \
      org.opencontainers.image.version=${VERSION} \
      org.opencontainers.image.licenses="MIT" \
      org.opencontainers.image.title="Fin-Tracker" \
      org.opencontainers.image.description="Household Finance Manager"

# Install build dependencies
RUN apk add --no-cache sqlite-dev nodejs npm

# App setup
WORKDIR /app
COPY . /app
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader \
    && npm install \
    && npm run build \
    && php artisan storage:link \
    && php artisan filament:optimize \
    && chown -R application:application storage bootstrap/cache

# Move configuration files for container entrypoint setup
RUN mv /app/.docker/worker.conf /opt/docker/etc/supervisor.d/worker.conf \
    && mv /app/.docker/artisan.sh /opt/docker/provision/entrypoint.d/artisan.sh

# Clean up unnecessary files and remove build dependencies
RUN rm -rf .docker *.js \
    && apk del sqlite-dev nodejs npm

# Healthcheck configuration
HEALTHCHECK --interval=60s --timeout=10s --start-period=10s --retries=1 \
  CMD curl --fail http://localhost/up || exit 1
