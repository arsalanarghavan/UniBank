---
sidebar_position: 3
---

# Deployment

## aaPanel (one-line)

On a server with Docker (aaPanel App Store → Docker):

```bash
curl -fsSL https://raw.githubusercontent.com/arsalanarghavan/UniBank/main/aapanel-install.sh | bash
```

With your domain:

```bash
curl -fsSL https://raw.githubusercontent.com/arsalanarghavan/UniBank/main/aapanel-install.sh | bash -s -- --domain example.com
```

What it does:

1. Clones [UniBank](https://github.com/arsalanarghavan/UniBank) into `/www/dk_project/UniBank` (override with `--dir`)
2. Creates `.env` / `backend/.env` (random DB passwords on first install)
3. Builds and starts Compose services
4. Waits for MariaDB health, generates `APP_KEY`, migrates & seeds

### Ports & firewall

Open (or reverse-proxy) these host ports:

| Port | Service |
|------|---------|
| 3000 | Next.js web |
| 8000 | Laravel API |
| 3001 | Docusaurus docs |
| 3306 | MariaDB (prefer keep internal) |
| 6379 | Redis (prefer keep internal) |

### Reverse proxy (aaPanel website)

Typical mapping when using `--domain example.com`:

- `example.com` → `127.0.0.1:3000`
- `api.example.com` → `127.0.0.1:8000`
- `docs.example.com` → `127.0.0.1:3001`

Enable SSL in aaPanel for each host. Sanctum/CORS are set from `--domain` automatically.

### Update

```bash
cd /www/dk_project/UniBank
./update.sh
```

Or re-run the one-liner (pulls + rebuilds):

```bash
curl -fsSL https://raw.githubusercontent.com/arsalanarghavan/UniBank/main/aapanel-install.sh | bash -s -- --dir /www/dk_project/UniBank --domain example.com
```

## Docker Compose (manual)

From the repository root:

```bash
docker compose up -d --build
```

Or locally after clone:

```bash
./install.sh
```

Services:

- `api` — Laravel + nginx
- `web` — Next.js 14.2.35
- `docs` — Docusaurus
- `queue` — queue worker
- `scheduler` — schedule worker (DB backup every 30 minutes)
- `db` — MariaDB 10.11
- `redis` — cache/queue/session
- `traefik` — optional (`--profile prod`)

Set `TELEGRAM_BOT_TOKEN`, channel IDs, and owner credentials before production.

Default seeded owner: `owner@ostadbank.local` / `ChangeMeNow!123`
