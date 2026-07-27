#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")" && pwd)"
cd "$ROOT"

git pull --ff-only || true
docker compose build api web docs
docker compose up -d
docker compose exec -T api php artisan migrate --force
docker compose exec -T api php artisan config:cache || true
docker compose exec -T api php artisan route:cache || true

echo "Update complete."
