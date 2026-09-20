#!/usr/bin/env sh
set -eu

# public/ is shared with nginx through a named volume. Docker only seeds such a
# volume from the image when it is empty, so on an upgrade it would still hold
# the previous release's assets. Refresh it from the image on every start, then
# drop files the new release no longer ships.
if [ -d /var/www/html/public ]; then
    cp -a /opt/public/. /var/www/html/public/
    find /var/www/html/public -type f | sed 's|^/var/www/html/public/||' | sort > /tmp/live.txt
    find /opt/public -type f | sed 's|^/opt/public/||' | sort > /tmp/shipped.txt
    comm -23 /tmp/live.txt /tmp/shipped.txt | while IFS= read -r stale; do
        [ -n "$stale" ] && rm -f "/var/www/html/public/$stale"
    done
    rm -f /tmp/live.txt /tmp/shipped.txt
fi

# Caches are built at container start, not at image build: they bake in runtime
# .env values, so building them earlier would freeze the wrong configuration.
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Idempotent; recreated here because the storage volume is mounted at runtime
# and so cannot carry a symlink made during the build.
php artisan storage:link --force

exec "$@"
