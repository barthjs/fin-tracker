#!/bin/bash

function setup_dev_commands {
    composer install
    npm install
    npm run build

    php artisan key:generate
    php artisan migrate:fresh --seed
    php artisan storage:link
}

cp .env.development .env
docker compose -f compose.dev.yaml up -d
docker compose exec fin-tracker bash -c "$(declare -f setup_dev_commands); setup_dev_commands"

exit 0
