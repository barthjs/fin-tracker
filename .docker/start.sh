#!/bin/bash

if [ -z "$APP_KEY" ]; then
    echo "Error: APP_KEY is not set. Please set the APP_KEY environment variable."
    exit 1
fi

echo "Caching the configuration..."
if ! php artisan config:cache; then
    echo "Error: Failed to cache the configuration."
    exit 1
fi

echo "Starting database migrations..."
if ! php artisan migrate --force; then
    echo "Error: Migration failed."
    exit 1
fi

echo "Running database seeding..."
if ! php artisan db:seed --force; then
    echo "Error: Seeding failed."
    exit 1
fi

echo "#############################"
echo "Setup completed successfully."
echo "#############################"
