#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")" && pwd)"
cd "$ROOT"

if [[ ! -f .env ]]; then
  cp .env.example .env
  echo "Created .env from .env.example"
fi

if [[ ! -f backend/.env ]]; then
  cp backend/.env.example backend/.env 2>/dev/null || cp .env.example backend/.env
fi

docker compose up -d --build db redis
echo "Waiting for database..."
sleep 15
docker compose up -d --build api queue scheduler web docs

echo "OstadBank is starting:"
echo "  Web:  http://localhost:3000"
echo "  API:  http://localhost:8000"
echo "  Docs: http://localhost:3001"
echo "  OpenAPI: http://localhost:8000/docs/api"
