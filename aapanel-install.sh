#!/usr/bin/env bash
# OstadBank / UniBank — aaPanel & remote one-line installer
# Usage:
#   curl -fsSL https://raw.githubusercontent.com/arsalanarghavan/UniBank/main/aapanel-install.sh | bash
#   curl -fsSL ... | bash -s -- --dir /www/dk_project/UniBank --domain example.com
#   ./aapanel-install.sh --dir "$(pwd)" --skip-clone   # local checkout
set -euo pipefail

REPO_URL="${REPO_URL:-https://github.com/arsalanarghavan/UniBank.git}"
BRANCH="${BRANCH:-main}"
INSTALL_DIR="${INSTALL_DIR:-/www/dk_project/UniBank}"
DOMAIN="${DOMAIN:-}"
SKIP_CLONE=0
INSTALL_DOCKER=0
PULL_ON_EXISTING=1

log()  { printf '\033[1;34m[ostadbank]\033[0m %s\n' "$*"; }
warn() { printf '\033[1;33m[ostadbank]\033[0m %s\n' "$*" >&2; }
die()  { printf '\033[1;31m[ostadbank]\033[0m %s\n' "$*" >&2; exit 1; }

usage() {
  cat <<'EOF'
OstadBank aaPanel installer

Options:
  --dir PATH           Install directory (default: /www/dk_project/UniBank)
  --domain HOST        Public domain (sets web/api/docs HTTPS URLs + CORS/Sanctum)
  --repo URL           Git repository URL
  --branch NAME        Git branch (default: main)
  --skip-clone         Use existing directory; do not clone/pull
  --no-pull            If directory exists, do not git pull
  --install-docker     Attempt to install Docker Engine + Compose plugin (Linux)
  -h, --help           Show help
EOF
}

parse_args() {
  while [[ $# -gt 0 ]]; do
    case "$1" in
      --dir) INSTALL_DIR="$2"; shift 2 ;;
      --domain) DOMAIN="$2"; shift 2 ;;
      --repo) REPO_URL="$2"; shift 2 ;;
      --branch) BRANCH="$2"; shift 2 ;;
      --skip-clone) SKIP_CLONE=1; shift ;;
      --no-pull) PULL_ON_EXISTING=0; shift ;;
      --install-docker) INSTALL_DOCKER=1; shift ;;
      -h|--help) usage; exit 0 ;;
      *) die "Unknown option: $1 (use --help)" ;;
    esac
  done
}

need_cmd() {
  command -v "$1" >/dev/null 2>&1 || die "Missing required command: $1"
}

random_secret() {
  if command -v openssl >/dev/null 2>&1; then
    openssl rand -hex 16
  else
    head -c 32 /dev/urandom | od -An -tx1 | tr -d ' \n' | head -c 32
  fi
}

set_env_key() {
  local file="$1" key="$2" value="$3"
  local tmp
  touch "$file"
  tmp="$(mktemp)"
  if grep -qE "^${key}=" "$file" 2>/dev/null; then
    awk -v k="$key" -v v="$value" '
      BEGIN { done=0 }
      index($0, k "=") == 1 && !done { print k "=" v; done=1; next }
      { print }
      END { if (!done) print k "=" v }
    ' "$file" >"$tmp"
  else
    cat "$file" >"$tmp"
    printf '%s=%s\n' "$key" "$value" >>"$tmp"
  fi
  mv "$tmp" "$file"
}

get_env_key() {
  local file="$1" key="$2"
  [[ -f "$file" ]] || { echo ""; return 0; }
  grep -E "^${key}=" "$file" | head -1 | cut -d= -f2- || true
}

try_install_docker() {
  [[ "$INSTALL_DOCKER" -eq 1 ]] || return 0
  if command -v docker >/dev/null 2>&1 && docker compose version >/dev/null 2>&1; then
    log "Docker already available"
    return 0
  fi
  need_cmd curl
  log "Installing Docker Engine (official convenience script)..."
  curl -fsSL https://get.docker.com | sh
  systemctl enable --now docker 2>/dev/null || service docker start 2>/dev/null || true
  if ! docker compose version >/dev/null 2>&1; then
    die "Docker installed but 'docker compose' plugin is missing. Install docker-compose-plugin."
  fi
}

ensure_prereqs() {
  try_install_docker
  need_cmd git
  need_cmd docker
  if ! docker compose version >/dev/null 2>&1; then
    die "Docker Compose plugin required. On aaPanel: App Store → Docker → enable Compose."
  fi
  if ! docker info >/dev/null 2>&1; then
    die "Cannot talk to Docker daemon. Start Docker or run as a user in the docker group."
  fi
}

clone_or_update() {
  if [[ "$SKIP_CLONE" -eq 1 ]]; then
    [[ -d "$INSTALL_DIR" ]] || die "--skip-clone requires existing directory: $INSTALL_DIR"
    [[ -f "$INSTALL_DIR/docker-compose.yml" ]] || die "Not an OstadBank checkout: $INSTALL_DIR"
    log "Using existing checkout: $INSTALL_DIR"
    return 0
  fi

  mkdir -p "$(dirname "$INSTALL_DIR")"
  if [[ -d "$INSTALL_DIR/.git" ]]; then
    log "Repository exists at $INSTALL_DIR"
    if [[ "$PULL_ON_EXISTING" -eq 1 ]]; then
      log "Pulling latest ($BRANCH)..."
      git -C "$INSTALL_DIR" fetch --depth 1 origin "$BRANCH" || warn "git fetch failed"
      git -C "$INSTALL_DIR" checkout "$BRANCH" || true
      git -C "$INSTALL_DIR" pull --ff-only origin "$BRANCH" || warn "git pull --ff-only failed (continuing with local tree)"
    fi
  elif [[ -d "$INSTALL_DIR" ]] && [[ -n "$(ls -A "$INSTALL_DIR" 2>/dev/null || true)" ]]; then
    die "Directory exists and is not a git repo: $INSTALL_DIR"
  else
    log "Cloning $REPO_URL ($BRANCH) → $INSTALL_DIR"
    git clone --branch "$BRANCH" --depth 1 "$REPO_URL" "$INSTALL_DIR"
  fi
}

setup_env_files() {
  local root="$INSTALL_DIR"
  local first_install=0

  if [[ ! -f "$root/.env" ]]; then
    first_install=1
    cp "$root/.env.example" "$root/.env"
    log "Created .env"
  fi
  if [[ ! -f "$root/backend/.env" ]]; then
    first_install=1
    if [[ -f "$root/backend/.env.example" ]]; then
      cp "$root/backend/.env.example" "$root/backend/.env"
    else
      cp "$root/.env.example" "$root/backend/.env"
    fi
    log "Created backend/.env"
  fi

  if [[ "$first_install" -eq 1 ]]; then
    local db_pass root_pass
    db_pass="$(random_secret)"
    root_pass="$(random_secret)"
    set_env_key "$root/.env" "DB_PASSWORD" "$db_pass"
    set_env_key "$root/.env" "DB_ROOT_PASSWORD" "$root_pass"
    set_env_key "$root/backend/.env" "DB_PASSWORD" "$db_pass"
    set_env_key "$root/backend/.env" "DB_USERNAME" "$(get_env_key "$root/.env" DB_USERNAME || echo ostadbank)"
    set_env_key "$root/backend/.env" "DB_DATABASE" "$(get_env_key "$root/.env" DB_DATABASE || echo ostadbank)"
    set_env_key "$root/backend/.env" "DB_HOST" "db"
    set_env_key "$root/backend/.env" "REDIS_HOST" "redis"
    log "Generated random DB passwords for first install"
  else
    # Keep backend DB/redis hosts aligned with compose network names
    set_env_key "$root/backend/.env" "DB_HOST" "db"
    set_env_key "$root/backend/.env" "REDIS_HOST" "redis"
    local shared_pass
    shared_pass="$(get_env_key "$root/.env" DB_PASSWORD)"
    if [[ -n "$shared_pass" ]]; then
      set_env_key "$root/backend/.env" "DB_PASSWORD" "$shared_pass"
    fi
  fi

  if [[ -n "$DOMAIN" ]]; then
    local web_url api_url docs_url
    web_url="https://${DOMAIN}"
    api_url="https://api.${DOMAIN}"
    docs_url="https://docs.${DOMAIN}"
    set_env_key "$root/.env" "NEXT_PUBLIC_APP_URL" "$web_url"
    set_env_key "$root/.env" "NEXT_PUBLIC_API_URL" "$api_url"
    set_env_key "$root/.env" "API_URL" "$api_url"
    set_env_key "$root/.env" "WEB_HOST" "$DOMAIN"
    set_env_key "$root/.env" "API_HOST" "api.${DOMAIN}"
    set_env_key "$root/.env" "DOCS_HOST" "docs.${DOMAIN}"
    set_env_key "$root/.env" "SANCTUM_STATEFUL_DOMAINS" "${DOMAIN},api.${DOMAIN},www.${DOMAIN}"
    set_env_key "$root/.env" "CORS_ALLOWED_ORIGINS" "$web_url"
    set_env_key "$root/backend/.env" "APP_URL" "$api_url"
    set_env_key "$root/backend/.env" "SANCTUM_STATEFUL_DOMAINS" "${DOMAIN},api.${DOMAIN},www.${DOMAIN}"
    set_env_key "$root/backend/.env" "CORS_ALLOWED_ORIGINS" "$web_url"
    log "Configured domain URLs for $DOMAIN (web / api. / docs.)"
  else
    set_env_key "$root/backend/.env" "APP_URL" "$(get_env_key "$root/.env" API_URL || echo http://localhost:8000)"
    set_env_key "$root/backend/.env" "SANCTUM_STATEFUL_DOMAINS" "$(get_env_key "$root/.env" SANCTUM_STATEFUL_DOMAINS || echo localhost:3000,localhost)"
    set_env_key "$root/backend/.env" "CORS_ALLOWED_ORIGINS" "$(get_env_key "$root/.env" CORS_ALLOWED_ORIGINS || echo http://localhost:3000)"
  fi
}

wait_db_healthy() {
  local root="$INSTALL_DIR"
  local i status
  log "Waiting for database health..."
  for i in $(seq 1 60); do
    status="$(docker compose -f "$root/docker-compose.yml" --project-directory "$root" ps --format json db 2>/dev/null | head -1 || true)"
    if docker compose -f "$root/docker-compose.yml" --project-directory "$root" exec -T db healthcheck.sh --connect --innodb_initialized >/dev/null 2>&1 \
      || docker inspect -f '{{.State.Health.Status}}' ostadbank_db 2>/dev/null | grep -qx healthy; then
      log "Database is healthy"
      return 0
    fi
    sleep 3
  done
  die "Database did not become healthy in time. Check: docker compose -f $root/docker-compose.yml logs db"
}

compose_up() {
  local root="$INSTALL_DIR"
  cd "$root"
  log "Building & starting db + redis..."
  docker compose up -d --build db redis
  wait_db_healthy
  log "Building & starting api, queue, scheduler, web, docs..."
  docker compose up -d --build api queue scheduler web docs
}

ensure_app_key() {
  local root="$INSTALL_DIR"
  local key
  key="$(get_env_key "$root/backend/.env" APP_KEY)"
  if [[ -z "$key" || "$key" == "base64:" ]]; then
    log "Generating Laravel APP_KEY..."
    docker compose -f "$root/docker-compose.yml" --project-directory "$root" exec -T api php artisan key:generate --force
  else
    log "APP_KEY already set"
  fi
}

migrate_seed() {
  local root="$INSTALL_DIR"
  log "Running migrations & seed (idempotent)..."
  docker compose -f "$root/docker-compose.yml" --project-directory "$root" exec -T api php artisan migrate --force --no-interaction || warn "migrate reported errors"
  docker compose -f "$root/docker-compose.yml" --project-directory "$root" exec -T api php artisan db:seed --force --no-interaction || warn "seed reported errors"
}

print_summary() {
  local root="$INSTALL_DIR"
  local web api docs owner_email owner_pass
  if [[ -n "$DOMAIN" ]]; then
    web="https://${DOMAIN}"
    api="https://api.${DOMAIN}"
    docs="https://docs.${DOMAIN}"
  else
    web="$(get_env_key "$root/.env" NEXT_PUBLIC_APP_URL || echo http://SERVER_IP:3000)"
    api="$(get_env_key "$root/.env" API_URL || echo http://SERVER_IP:8000)"
    docs="http://SERVER_IP:3001"
  fi
  owner_email="$(get_env_key "$root/backend/.env" OWNER_EMAIL || echo owner@ostadbank.local)"
  owner_pass="$(get_env_key "$root/backend/.env" OWNER_PASSWORD || echo 'ChangeMeNow!123')"

  cat <<EOF

============================================================
  OstadBank install complete
============================================================
  Install dir : $root
  Web         : $web  (host port 3000)
  API         : $api  (host port 8000)
  Docs        : $docs (host port 3001)
  OpenAPI     : ${api%/}/docs/api

  Owner login :
    Email    : $owner_email
    Password : $owner_pass

  aaPanel tips:
    - Open firewall ports 3000 / 8000 / 3001 (or reverse-proxy to them)
    - Point $DOMAIN → web, api.$DOMAIN → API, docs.$DOMAIN → docs
    - Update later:  cd $root && ./update.sh

  One-liner reinstall/update from GitHub:
    curl -fsSL https://raw.githubusercontent.com/arsalanarghavan/UniBank/main/aapanel-install.sh | bash -s -- --dir $root${DOMAIN:+ --domain $DOMAIN}
============================================================
EOF
}

main() {
  parse_args "$@"
  ensure_prereqs
  clone_or_update
  setup_env_files
  compose_up
  ensure_app_key
  migrate_seed
  print_summary
}

main "$@"
