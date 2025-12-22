#!/bin/sh

if [ -z "$APP_KEY" ]; then
    echo "Error: APP_KEY is not set. Please set the APP_KEY environment variable."
    exit 1
fi

if [ ! -d "/app/public/vendor/livewire" ]; then
    php artisan livewire:publish --assets
fi

if [ ! -d "/app/public/vendor/filament" ]; then
    php artisan filament:assets
fi

if ! php artisan optimize; then
    echo "Error: App optimization failed."
    exit 1
fi

if ! php artisan migrate --force; then
    echo "Error: Database migrations failed."
    exit 1
fi

if ! php artisan db:seed --force; then
    echo "Error: Database seeding failed."
    exit 1
fi

chown -R application:application /app/storage/app/

echo "#############################"
echo "Setup completed successfully."
echo "#############################"
