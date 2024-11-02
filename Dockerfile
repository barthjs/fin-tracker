# Base image for PHP and NGINX
FROM webdevops/php-nginx:8.3-alpine

# Fetch the latest release version from GitHub API
ARG GITHUB_API_URL=https://api.github.com/repos/barthjs/fin-tracker/releases/latest
RUN apk add --no-cache jq curl nodejs npm \
    && export APP_VERSION=$(curl -s $GITHUB_API_URL | jq -r .tag_name) \
    && echo "APP_VERSION=${APP_VERSION}" > /.env

# Labels
LABEL vendor="barthjs" \
      maintainer="barthjs" \
      org.opencontainers.image.url="https://github.com/barthjs/fin-tracker" \
      org.opencontainers.image.source="https://github.com/barthjs/fin-tracker" \
      org.opencontainers.image.license="MIT" \
      org.opencontainers.image.title="Fin-Tracker" \
      org.opencontainers.image.description="Household Finance Manager"

ENV WEB_DOCUMENT_ROOT=/app/public

# Update npm to the latest version
RUN npm install -g npm@latest

# App setup
WORKDIR /app
ADD https://github.com/barthjs/fin-tracker.git /app
RUN mv /.env /app/.env \
    && composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader \
    && npm install \
    && npm run build \
    && php artisan storage:link \
    && php artisan filament:optimize \
    && chown -R application:application storage

# Copy configuration files for container entrypoint setup
RUN mv /app/.docker/worker.conf /opt/docker/etc/supervisor.d/worker.conf \
    && mv /app/.docker/artisan.sh /opt/docker/provision/entrypoint.d/artisan.sh

# Clean up unnecessary files and remove build dependencies
RUN rm -rf .git .github .run storage/debugbar tests .editorconfig .gitattributes .gitignore *.yaml phpunit.xml setup-dev.sh *.js \
    && apk del git jq nodejs npm

# Healthcheck configuration
HEALTHCHECK --interval=60s --timeout=10s --start-period=10s --retries=1 \
  CMD curl --fail http://localhost/up || exit 1
