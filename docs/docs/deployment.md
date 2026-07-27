---
sidebar_position: 3
---

# Deployment

Use Docker Compose from the repository root:

```bash
docker compose up -d --build
```

Services:

- `api` — Laravel + nginx
- `web` — Next.js 14.2.35
- `docs` — Docusaurus
- `queue` — queue worker
- `scheduler` — schedule worker (DB backup every 30 minutes)
- `db` — MariaDB 10.11
- `redis` — cache/queue/session

Set `TELEGRAM_BOT_TOKEN`, channel IDs, and owner credentials before production.
