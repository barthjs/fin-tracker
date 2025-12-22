#!/bin/sh

## PHP
if [ -n "$PHP_MEMORY_LIMIT" ]; then
    sed -i "s/memory_limit = .*/memory_limit = $PHP_MEMORY_LIMIT/" /usr/local/etc/php/conf.d/php.ini
fi

## PHP-FPM
if [ -n "$PHP_FPM_MAX_CHILDREN" ]; then
    sed -i "s/pm.max_children = .*/pm.max_children = $PHP_FPM_MAX_CHILDREN/" /usr/local/etc/php-fpm.d/application.conf
fi
if [ -n "$PHP_FPM_START_SERVERS" ]; then
    sed -i "s/pm.start_servers = .*/pm.start_servers = $PHP_FPM_START_SERVERS/" /usr/local/etc/php-fpm.d/application.conf
fi
if [ -n "$PHP_FPM_MIN_SPARE_SERVERS" ]; then
    sed -i "s/pm.min_spare_servers = .*/pm.min_spare_servers = $PHP_FPM_MIN_SPARE_SERVERS/" /usr/local/etc/php-fpm.d/application.conf
fi
if [ -n "$PHP_FPM_MAX_SPARE_SERVERS" ]; then
    sed -i "s/pm.max_spare_servers = .*/pm.max_spare_servers = $PHP_FPM_MAX_SPARE_SERVERS/" /usr/local/etc/php-fpm.d/application.conf
fi

## Nginx
if [ -n "$NGINX_WORKERS" ]; then
    sed -i "s/worker_processes .*/worker_processes $NGINX_WORKERS;/" /etc/nginx/nginx.conf
fi
if [ -n "$NGINX_WORKER_CONNECTIONS" ]; then
    sed -i "s/worker_connections .*/worker_connections $NGINX_WORKER_CONNECTIONS;/" /etc/nginx/nginx.conf
fi

## Worker
if [ -n "$WORKER_COUNT" ]; then
    sed -i "s/numprocs=.*/numprocs=$WORKER_COUNT/" /etc/supervisor.d/worker.conf
fi
