#!/bin/bash
if [ ! -f ".env" ]; then
    echo "APP_KEY=" >>.env
    php artisan key:generate --force
fi

php artisan migrate --force
php artisan db:seed --force
php artisan optimize

if [ -f "storage/logs/laravel.log" ]; then
    chown application:application storage/logs/laravel.log
fi

chown application:application .env
