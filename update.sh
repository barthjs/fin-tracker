#!/bin/bash
FORCE=false
if [[ "$1" == "-f" ]]; then
    FORCE=true
fi

if [ "$FORCE" = false ] && [ ! -f update.lock ]; then
    echo "Lock not found. No deployment will take place."
    exit 0
fi

exec >update.log 2>&1
rm update.lock

# Reset and pull the latest code from GitHub
git reset --hard
git pull origin main

# Execute update commands inside the Docker container
docker compose exec app bash -c "
    php artisan down

    service php-fpm restart

    composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
    npm install
    npm run build

    php artisan migrate --force
    php artisan optimize:clear
    php artisan optimize
    php artisan filament:optimize-clear
    php artisan filament:optimize

    php artisan up
"
exit 0
