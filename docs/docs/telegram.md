---
sidebar_position: 5
---

# Telegram module

Webhook:

```http
POST /api/telegram/webhook
X-Telegram-Bot-Api-Secret-Token: <TELEGRAM_WEBHOOK_SECRET>
```

Implemented with Nutgram inside `app/Modules/Telegram`.

## Features

- `/start` welcome + main keyboard
- Force-subscribe gate via `settings.force_subscribe` + `required_channels`
- My experiences / rules / ranking / search prompts
- `/admin` panel for admins (pending list, stats, approve/reject)
- Approved experiences published to configured channel
- Broadcast and DM jobs via Redis queue
- `php artisan ostadbank:backup-db` every 30 minutes via scheduler

Users created from Telegram get `telegram_id` and role `student`.
