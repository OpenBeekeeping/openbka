#!/usr/bin/env bash
# OpenBKA installer (Docker). Run on a fresh Linux server as root:
#   curl -fsSL https://raw.githubusercontent.com/OpenBeekeeping/openbka/main/deploy/install.sh | sudo bash
#
# Before you start, point your domain's DNS (an A record) at this server.
set -euo pipefail

DIR=/opt/openbka
REPO_RAW=https://raw.githubusercontent.com/OpenBeekeeping/openbka/main

say() { printf '\n\033[1;33m%s\033[0m\n' "$*"; }
ask() { local v; read -rp "$1: " v </dev/tty; printf '%s' "$v"; }

[ "$(id -u)" -eq 0 ] || { echo "Error: run this as root (sudo)."; exit 1; }

if ! command -v docker >/dev/null; then
  say "Installing Docker"
  curl -fsSL https://get.docker.com | sh
fi

mkdir -p "$DIR" && cd "$DIR"

if [ -f .env ]; then
  say "OpenBKA is already installed in $DIR. Updating it."
  curl -fsSL "$REPO_RAW/deploy/docker-compose.yml" -o docker-compose.yml
  docker compose pull && docker compose up -d
  exit 0
fi

say "Set up OpenBKA"
DOMAIN=$(ask "Domain for OpenBKA (e.g. members.yourbka.org.uk)")
ASSOCIATION=$(ask "Association name")
NAME=$(ask "Your name")
EMAIL=$(ask "Your email")

say "Email (used to send sign-in links)"
echo "Enter your SMTP details. You can change them later in $DIR/.env."
SMTP_HOST=$(ask "SMTP host")
SMTP_PORT=$(ask "SMTP port [587]"); SMTP_PORT=${SMTP_PORT:-587}
SMTP_USER=$(ask "SMTP username")
SMTP_PASS=$(ask "SMTP password")
MAIL_FROM=$(ask "Send emails from [$EMAIL]"); MAIL_FROM=${MAIL_FROM:-$EMAIL}

curl -fsSL "$REPO_RAW/deploy/docker-compose.yml" -o docker-compose.yml

umask 077
cat > .env <<ENV
APP_NAME="OpenBKA"
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:$(openssl rand -base64 32)
APP_URL=https://$DOMAIN
OPENBKA_DOMAIN=$DOMAIN
DB_PASSWORD=$(openssl rand -hex 24)

MAIL_MAILER=smtp
MAIL_HOST=$SMTP_HOST
MAIL_PORT=$SMTP_PORT
MAIL_USERNAME=$SMTP_USER
MAIL_PASSWORD="$SMTP_PASS"
MAIL_FROM_ADDRESS=$MAIL_FROM
MAIL_FROM_NAME="$ASSOCIATION"
ENV

say "Starting OpenBKA (this can take a minute)"
docker compose up -d
until docker compose exec -T app php artisan migrate:status >/dev/null 2>&1; do sleep 3; done

docker compose exec -T app php artisan openbka:install \
  --association="$ASSOCIATION" --name="$NAME" --email="$EMAIL"

say "Done. OpenBKA is at https://$DOMAIN"
echo "Update later by running this script again, or: cd $DIR && docker compose pull && docker compose up -d"
