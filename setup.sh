#!/bin/bash

set -e
set -o pipefail

function handle_error {
    echo "Error in script at line $1"
    exit 1
}

trap 'handle_error $LINENO' ERR

function in_docker {
    if command -v docker &>/dev/null; then
        return 0
    else
        return 1
    fi
}

function setup_dev_commands {
    composer install
    npm install
    npm run build

    php artisan key:generate
    php artisan migrate:fresh --seed
    php artisan storage:link
}

function setup_prod_commands {
    composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
    npm install
    npm run build

    php artisan key:generate --force
    php artisan migrate:fresh --force
    php artisan db:seed --force
    php artisan storage:link
    php artisan optimize
}

function setup_dev {
    cp .env.development .env
    if ! in_docker; then
        echo "Running development setup inside Docker container..."
        setup_dev_commands
    else
        echo "Creating containers"
        docker compose -f compose.dev.yaml up -d || {
            echo 'Error starting compose.dev.yaml'
            exit 1
        }
        echo "Running development setup on host via Docker..."
        docker compose exec app bash -c "$(declare -f setup_dev_commands); setup_dev_commands"
    fi
}

function setup_prod {
    if ! in_docker; then
        echo "Running production setup inside Docker container..."
        setup_prod_commands
    else
        echo "Creating containers"
        docker compose -f compose.yaml up -d || {
            echo 'Error starting compose.yaml'
            exit 1
        }
        echo "Running production setup on host via Docker..."
        docker compose exec app bash -c "$(declare -f setup_prod_commands); setup_prod_commands"
    fi
}

# Main script logic
if [[ "$1" == "dev" ]]; then
    echo "Creating development environment with: compose.dev.yaml"
    setup_dev
else
    echo "Creating production environment with: compose.yaml"
    setup_prod
fi
exit 0
