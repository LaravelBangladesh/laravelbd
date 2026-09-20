#!/usr/bin/env sh
set -eu

# Caches are built at container start, not at image build: they bake in runtime
# .env values, so building them earlier would freeze the wrong configuration.
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Idempotent; recreated here because the storage volume is mounted at runtime
# and so cannot carry a symlink made during the build.
php artisan storage:link --force

exec "$@"
