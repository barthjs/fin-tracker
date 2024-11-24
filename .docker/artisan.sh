#!/bin/bash

# Generate the APP_KEY
if ! grep -q "^APP_KEY=" ".env"; then
    echo "Generating APP_KEY..."
    echo "APP_KEY=" >> .env
    if ! php artisan key:generate --force; then
        echo "Error: Failed to generate APP_KEY."
        exit 1
    fi
    chmod 600 .env
    chown application:application .env
    echo ".env file created and APP_KEY generated successfully."
fi

# Run migrations and seeds
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

# Optimize the application
echo "Optimizing the application..."
if ! php artisan optimize; then
    echo "Error: Optimization failed."
    exit 1
fi

echo "Setup completed successfully."
