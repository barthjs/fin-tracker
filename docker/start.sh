#!/bin/sh

for dir in "/entrypoint.d" "/entrypoint.d/custom"; do
    [ -d "$dir" ] || continue

    for f in "$dir"/*.sh; do
        [ -e "$f" ] || continue
        if [ -x "$f" ]; then
            "$f"
        elif [ -f "$f" ]; then
            . "$f"
        fi
    done
done

supervisord -c /etc/supervisord.conf
