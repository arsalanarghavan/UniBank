# OstadBank

Professor Archive platform — Laravel 13 API, Next.js 14.2.35 + official shadcn/ui, Docusaurus docs, and Telegram/Bale bot module.

Repository: [github.com/arsalanarghavan/UniBank](https://github.com/arsalanarghavan/UniBank)

## Architecture

| Service | Path | Port |
|---------|------|------|
| API (Laravel) | `backend/` | 8000 |
| Web (Next.js) | `frontend/` | 3000 |
| Docs (Docusaurus) | `docs/` | 3001 |
| MariaDB | Docker | 3306 |
| Redis | Docker | 6379 |

## aaPanel Compose form

Paste-ready fields (Compose Name / YAML / `.env` / Notes) for the aaPanel Docker Compose UI: [`aapanel/FORM.md`](aapanel/FORM.md).

Project name `ostadbank` → `/www/dk_project/ostadbank`. Clone the repo into that path before Start (builds need `backend/`, `frontend/`, `docs/`).

## aaPanel one-line install

Requires Docker + Compose on the server (aaPanel → App Store → Docker).

```bash
curl -fsSL https://raw.githubusercontent.com/arsalanarghavan/UniBank/main/aapanel-install.sh | bash
```

With domain (sets `web` / `api.` / `docs.` HTTPS URLs + Sanctum/CORS):

```bash
curl -fsSL https://raw.githubusercontent.com/arsalanarghavan/UniBank/main/aapanel-install.sh | bash -s -- --domain example.com
```

Custom install path:

```bash
curl -fsSL https://raw.githubusercontent.com/arsalanarghavan/UniBank/main/aapanel-install.sh | bash -s -- --dir /www/dk_project/UniBank --domain example.com
```

Optional: also install Docker Engine if missing:

```bash
curl -fsSL https://raw.githubusercontent.com/arsalanarghavan/UniBank/main/aapanel-install.sh | bash -s -- --install-docker --domain example.com
```

Default install directory: `/www/dk_project/UniBank`

## Quick start (local / existing checkout)

```bash
cp .env.example .env
cp backend/.env.example backend/.env
./install.sh
# or: docker compose up -d --build
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
Telegram legacy webhook: `POST /api/telegram/webhook`  
Per-bot webhook: `POST /api/bots/{id}/webhook`

### Legacy import

Configure `LEGACY_DB_*` then:

```bash
php artisan db:seed --class=LegacyImportSeeder
```

## Modules

Domain modules under `backend/Modules/` (nwidart) and Telegram implementation under `backend/app/Modules/Telegram/`.

## Legacy Python bot

Previous Telegram bot sources are archived in `legacy/`.
