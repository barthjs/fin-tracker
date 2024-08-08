# Apache, Composer, PHP
ARG image=webdevops/php-apache:8.3-alpine
FROM ${image}

# Node.js, NPM
RUN apk add --no-cache nodejs npm
RUN npm install npm@latest -g

# Laravel scheduler
RUN docker-service enable cron
RUN echo "* * * * * cd /app && php artisan schedule:run >> /dev/null 2>&1" > /etc/crontabs/root

WORKDIR /app
