# OstadBank

Professor Archive platform — Laravel 13 API, Next.js 14.2.35 + official shadcn/ui, Docusaurus docs, and Telegram bot module.

## Architecture

| Service | Path | Port |
|---------|------|------|
| API (Laravel) | `backend/` | 8000 |
| Web (Next.js) | `frontend/` | 3000 |
| Docs (Docusaurus) | `docs/` | 3001 |
| MariaDB | Docker | 3306 |
| Redis | Docker | 6379 |

## Quick start

```bash
cp .env.example .env
cp backend/.env.example backend/.env   # or use the generated backend/.env
docker compose up -d --build
```

Default owner (seeded):

- Email: `owner@ostadbank.local`
- Password: `ChangeMeNow!123`

## Frontend

```bash
cd frontend
npm install
npm run dev
```

Official shadcn blocks used:

- `npx shadcn@latest add login-04`
- `npx shadcn@latest add sidebar-07`

Features: SSR, next-intl (`fa` RTL / `en` LTR), next-themes dark/light, accent tokens, Jalali date picker for Persian, Recharts admin charts, Lucide icons.

## Backend

```bash
cd backend
composer install
php artisan migrate --seed
php artisan serve
php artisan queue:work
php artisan schedule:work
```

API base: `/api/v1`  
OpenAPI (Scramble): `/docs/api`  
Telegram webhook: `POST /api/telegram/webhook`

### Legacy import

Configure `LEGACY_DB_*` then:

```bash
php artisan db:seed --class=LegacyImportSeeder
```

## Modules

Domain modules under `backend/Modules/` (nwidart) and Telegram implementation under `backend/app/Modules/Telegram/`.

## Legacy Python bot

Previous Telegram bot sources are archived in `legacy/`.
