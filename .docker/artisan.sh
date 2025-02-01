#!/bin/bash

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

echo "Optimizing the application..."
if ! php artisan optimize; then
    echo "Error: Optimization failed."
    exit 1
fi

echo "Setup completed successfully."
