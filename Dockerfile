# Apache, Composer, PHP
FROM webdevops/php-apache:8.3-alpine
ENV WEB_DOCUMENT_ROOT=/app/public

# Node.js, NPM
RUN apk add --no-cache nodejs npm
RUN npm install npm@latest -g

WORKDIR /app

# Install the app
COPY . .

# Install Composer and NPM packages
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
RUN npm install
RUN npm run build

RUN php artisan filament:optimize
RUN php artisan filament:cache-components
RUN php artisan icons:cache

COPY .docker/worker.conf /opt/docker/etc/supervisor.d/worker.conf
COPY .docker/artisan.sh /opt/docker/provision/entrypoint.d/artisan.sh

# Scheduler
RUN docker-service enable cron
RUN echo "* * * * * cd /app && php artisan schedule:run >> /dev/null 2>&1" > /etc/crontabs/root

RUN chown -R application:application .
