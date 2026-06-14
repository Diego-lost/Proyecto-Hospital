#!/bin/sh
set -e

echo "==> Clearing config cache..."
php artisan config:clear

echo "==> Running migrations..."
if php artisan migrate --force --no-interaction; then
  echo "==> Migrations OK"
else
  echo "==> WARNING: migrations failed (tablas ya en Supabase o revisa DB_* en Environment)."
fi

echo "==> Caching config..."
php artisan config:cache

echo "==> Starting server on port ${PORT:-10000}..."
exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
