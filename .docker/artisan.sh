#!/bin/bash

test_db_connection() {
    php -r "
        require '/app/vendor/autoload.php';
        use Illuminate\Support\Facades\DB;

        \$app = require_once '/app/bootstrap/app.php';
        \$kernel = \$app->make(Illuminate\Contracts\Console\Kernel::class);
        \$kernel->bootstrap();

        try {
            DB::connection()->getPdo();
            if (DB::connection()->getDatabaseName()) {
                exit(0);
            } else {
                exit(1);
            }
        } catch (Exception \$e) {
            exit(1);
        }
    "
}

# Check if .env file exists, if not, create it and generate the APP_KEY
if [ ! -f ".env" ]; then
    echo "Creating .env file and generating APP_KEY..."
    echo "APP_KEY=" >>.env
    if ! php artisan key:generate --force; then
        echo "Error: Failed to generate APP_KEY."
        exit 1
    fi
    chmod 600 .env
    chown application:application .env
    echo ".env file created and APP_KEY generated successfully."
fi

count=0
timeout=30

echo "Checking database connection..."
while [ "$count" -lt "$timeout" ]; do
    if test_db_connection >/dev/null 2>&1; then
        echo "Database connection successful."
        break
    else
        echo -ne "Waiting for database connection... ${count}/${timeout}s\r"
        count=$((count + 1))
        sleep 1
    fi
done

# If timeout is reached, exit with an error
if [ "$count" -eq "$timeout" ]; then
    echo "Database connection failed after $timeout seconds."
    exit 1
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
