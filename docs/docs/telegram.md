---
sidebar_position: 5
---

# Bots (Telegram + Bale)

Each university can own one bot per platform (`telegram` | `bale`).

## Webhooks

Per-bot webhook (preferred):

```http
POST /api/bots/{bot}/webhook
X-Telegram-Bot-Api-Secret-Token: <bot.webhook_secret>
```

Legacy single-bot webhook (uses `TELEGRAM_BOT_TOKEN`):

```http
POST /api/telegram/webhook
X-Telegram-Bot-Api-Secret-Token: <TELEGRAM_WEBHOOK_SECRET>
```

Implemented with Nutgram inside `app/Modules/Telegram`. Bale uses `services.bale.api_url` (default `https://tapi.bale.ai`).

## Features

- `/start` → upsert `users` with `telegram_id` or `bale_id`, role `student`, `signup_university_id`
- Force-join via per-bot `force_subscribe` setting + `required_channels`
- Main keyboard from Bot UI Studio (`bots.ui_layout.main_menu.rows`) with FA defaults fallback
- My experiences / rules / ranking / search prompts
- `/admin` panel for admins (pending list, stats, approve/reject)
- Approved experiences published to the bot's publish channel (fallback: global setting)
- Broadcast and DM jobs via Redis queue
- `php artisan ostadbank:backup-db` every 30 minutes via scheduler

## Admin UI

Dashboard → Bots → open a bot for texts, force-join channels, and UI Studio.
