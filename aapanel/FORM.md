# فرم Docker Compose در aaPanel — OstadBank

مقادیر زیر را در UI فرم Compose کپی کنید. مسیر پروژه: `/www/dk_project/ostadbank`

> **مهم:** فرم aaPanel فقط `docker-compose.yml` و `.env` می‌سازد. سرویس‌های `api` / `web` / `docs` به سورس ریپو نیاز دارند (`build: ./backend|frontend|docs`). **قبل از Start** ریپو را کلون کنید، یا از [`aapanel-install.sh`](../aapanel-install.sh) استفاده کنید.

فایل‌های مرجع همین فولدر:
- [`docker-compose.yml`](docker-compose.yml)
- [`.env.example`](.env.example)

---

## 1) Compose Name

```
ostadbank
```

---

## 2) Compose Content

```yaml
services:
  db:
    image: mariadb:10.11
    container_name: ostadbank_db
    restart: unless-stopped
    command:
      - "--character-set-server=utf8mb4"
      - "--collation-server=utf8mb4_unicode_ci"
    environment:
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD:-rootsecret}
      MYSQL_DATABASE: ${DB_DATABASE:-ostadbank}
      MYSQL_USER: ${DB_USERNAME:-ostadbank}
      MYSQL_PASSWORD: ${DB_PASSWORD:-secret}
    volumes:
      - mariadb_data:/var/lib/mysql
    healthcheck:
      test: ["CMD", "healthcheck.sh", "--connect", "--innodb_initialized"]
      interval: 10s
      timeout: 5s
      retries: 15
    networks:
      - ostadbank_net

  redis:
    image: redis:7-alpine
    container_name: ostadbank_redis
    restart: unless-stopped
    networks:
      - ostadbank_net

  api:
    build:
      context: ./backend
      dockerfile: Dockerfile
    container_name: ostadbank_api
    restart: unless-stopped
    env_file:
      - .env
    environment:
      APP_URL: ${API_URL:-http://localhost:8000}
      DB_CONNECTION: mysql
      DB_HOST: db
      DB_PORT: 3306
      DB_DATABASE: ${DB_DATABASE:-ostadbank}
      DB_USERNAME: ${DB_USERNAME:-ostadbank}
      DB_PASSWORD: ${DB_PASSWORD:-secret}
      REDIS_HOST: redis
      REDIS_PORT: 6379
      SESSION_DRIVER: ${SESSION_DRIVER:-redis}
      QUEUE_CONNECTION: ${QUEUE_CONNECTION:-redis}
      CACHE_STORE: ${CACHE_STORE:-redis}
      SANCTUM_STATEFUL_DOMAINS: ${SANCTUM_STATEFUL_DOMAINS:-localhost:3000,localhost}
      CORS_ALLOWED_ORIGINS: ${CORS_ALLOWED_ORIGINS:-http://localhost:3000}
    depends_on:
      db:
        condition: service_healthy
      redis:
        condition: service_started
    ports:
      - "${API_PORT:-8000}:80"
    networks:
      - ostadbank_net

  queue:
    build:
      context: ./backend
      dockerfile: Dockerfile
    container_name: ostadbank_queue
    restart: unless-stopped
    command: php artisan queue:work --sleep=1 --tries=3 --max-time=3600
    env_file:
      - .env
    environment:
      DB_HOST: db
      DB_DATABASE: ${DB_DATABASE:-ostadbank}
      DB_USERNAME: ${DB_USERNAME:-ostadbank}
      DB_PASSWORD: ${DB_PASSWORD:-secret}
      REDIS_HOST: redis
      QUEUE_CONNECTION: ${QUEUE_CONNECTION:-redis}
      CACHE_STORE: ${CACHE_STORE:-redis}
    depends_on:
      - api
      - redis
    networks:
      - ostadbank_net

  scheduler:
    build:
      context: ./backend
      dockerfile: Dockerfile
    container_name: ostadbank_scheduler
    restart: unless-stopped
    command: php artisan schedule:work
    env_file:
      - .env
    environment:
      DB_HOST: db
      DB_DATABASE: ${DB_DATABASE:-ostadbank}
      DB_USERNAME: ${DB_USERNAME:-ostadbank}
      DB_PASSWORD: ${DB_PASSWORD:-secret}
      REDIS_HOST: redis
    depends_on:
      - api
    networks:
      - ostadbank_net

  web:
    build:
      context: ./frontend
      dockerfile: Dockerfile
      args:
        NEXT_PUBLIC_API_URL: ${NEXT_PUBLIC_API_URL:-http://localhost:8000}
    container_name: ostadbank_web
    restart: unless-stopped
    environment:
      NEXT_PUBLIC_API_URL: ${NEXT_PUBLIC_API_URL:-http://localhost:8000}
      NEXT_PUBLIC_APP_URL: ${NEXT_PUBLIC_APP_URL:-http://localhost:3000}
      API_INTERNAL_URL: ${API_INTERNAL_URL:-http://api}
    ports:
      - "${WEB_PORT:-3000}:3000"
    depends_on:
      - api
    networks:
      - ostadbank_net

  docs:
    build:
      context: ./docs
      dockerfile: Dockerfile
    container_name: ostadbank_docs
    restart: unless-stopped
    ports:
      - "${DOCS_PORT:-3001}:3000"
    networks:
      - ostadbank_net

volumes:
  mariadb_data:

networks:
  ostadbank_net:
    driver: bridge
```

---

## 3) .env Content

`YOUR_SERVER_IP` را با IP یا دامنهٔ سرور عوض کنید.

```env
COMPOSE_PROJECT_NAME=ostadbank

# --- Laravel ---
APP_NAME=OstadBank
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://YOUR_SERVER_IP:8000
APP_LOCALE=fa
APP_FALLBACK_LOCALE=en

# --- Public URLs / ports ---
API_URL=http://YOUR_SERVER_IP:8000
API_PORT=8000
WEB_PORT=3000
DOCS_PORT=3001
NEXT_PUBLIC_API_URL=http://YOUR_SERVER_IP:8000
NEXT_PUBLIC_APP_URL=http://YOUR_SERVER_IP:3000
API_INTERNAL_URL=http://api

# --- Database (not published on host) ---
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=ostadbank
DB_USERNAME=ostadbank
DB_PASSWORD=ChangeMeDbPassword!
DB_ROOT_PASSWORD=ChangeMeRootPassword!

# --- Redis / session / cache / queue ---
REDIS_HOST=redis
REDIS_PORT=6379
SESSION_DRIVER=redis
SESSION_DOMAIN=
QUEUE_CONNECTION=redis
CACHE_STORE=redis

# --- CORS / Sanctum ---
SANCTUM_STATEFUL_DOMAINS=YOUR_SERVER_IP:3000,localhost:3000,localhost
CORS_ALLOWED_ORIGINS=http://YOUR_SERVER_IP:3000

# --- Default owner ---
OWNER_EMAIL=owner@ostadbank.local
OWNER_PASSWORD=ChangeMeNow!123
OWNER_NAME=Owner
OWNER_TELEGRAM_ID=

# --- Telegram (optional) ---
TELEGRAM_BOT_TOKEN=
TELEGRAM_CHANNEL_ID=
TELEGRAM_BACKUP_CHANNEL_ID=
TELEGRAM_WEBHOOK_SECRET=

# --- Bale ---
BALE_API_URL=https://tapi.bale.ai

# --- Legacy import (optional) ---
LEGACY_DB_HOST=
LEGACY_DB_PORT=3306
LEGACY_DB_DATABASE=
LEGACY_DB_USERNAME=
LEGACY_DB_PASSWORD=
```

---

## 4) Notes (Also Save as Template)

متن پیشنهادی برای فیلد Notes:

```
1. Docker را در aaPanel نصب کنید (App Store → Docker).
2. قبل از Start سورس را کلون کنید:
   git clone https://github.com/arsalanarghavan/UniBank.git /www/dk_project/ostadbank
3. محتوای Compose و .env را در این فرم paste کنید (یا فایل‌های aapanel/ را از ریپو کپی کنید).
4. پورت‌های 3000 / 8000 / 3001 را در فایروال باز کنید یا Reverse Proxy بزنید. DB و Redis روی host منتشر نمی‌شوند.
5. بعد از بالا آمدن:
   docker compose exec api php artisan key:generate --force
   docker compose exec api php artisan migrate --force
   docker compose exec api php artisan db:seed --force
6. Owner پیش‌فرض: owner@ostadbank.local / ChangeMeNow!123
7. جایگزین یک‌خطی:
   curl -fsSL https://raw.githubusercontent.com/arsalanarghavan/UniBank/main/aapanel-install.sh | bash
```

---

## بعد از Start (دستی)

```bash
cd /www/dk_project/ostadbank
docker compose exec api php artisan key:generate --force
docker compose exec api php artisan migrate --force
docker compose exec api php artisan db:seed --force
```
