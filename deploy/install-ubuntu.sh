#!/usr/bin/env bash
# OpenBKA installer for a fresh Ubuntu 24.04+ server, without Docker.
# Installs PHP 8.4, nginx, PostgreSQL and a free HTTPS certificate. Run as root:
#   curl -fsSL https://raw.githubusercontent.com/OpenBeekeeping/openbka/main/deploy/install-ubuntu.sh | sudo bash
#
# Before you start, point your domain's DNS (an A record) at this server.
set -euo pipefail

APP=/var/www/openbka
REPO=https://github.com/OpenBeekeeping/openbka.git
PHP=8.4

say() { printf '\n\033[1;33m%s\033[0m\n' "$*"; }
ask() { local v; read -rp "$1: " v </dev/tty; printf '%s' "$v"; }

[ "$(id -u)" -eq 0 ] || { echo "Error: run this as root (sudo)."; exit 1; }
. /etc/os-release
[ "$ID" = ubuntu ] || { echo "Error: this script is for Ubuntu. Use install.sh (Docker) instead."; exit 1; }

if [ -d "$APP/.git" ]; then
  say "Updating OpenBKA"
  cd "$APP"
  sudo -u www-data git fetch --tags --quiet
  TAG=$(sudo -u www-data git describe --tags "$(sudo -u www-data git rev-list --tags --max-count=1)" 2>/dev/null || echo main)
  sudo -u www-data git checkout --quiet "$TAG"
  sudo -u www-data composer install --no-dev --optimize-autoloader --no-interaction
  sudo -u www-data php artisan migrate --force
  sudo -u www-data php artisan optimize
  systemctl restart "php$PHP-fpm" openbka-queue
  say "Updated to $TAG"
  exit 0
fi

say "Set up OpenBKA"
DOMAIN=$(ask "Domain for OpenBKA (e.g. members.yourbka.org.uk)")
ASSOCIATION=$(ask "Association name")
NAME=$(ask "Your name")
EMAIL=$(ask "Your email")
SMTP_HOST=$(ask "SMTP host (for sign-in emails)")
SMTP_PORT=$(ask "SMTP port [587]"); SMTP_PORT=${SMTP_PORT:-587}
SMTP_USER=$(ask "SMTP username")
SMTP_PASS=$(ask "SMTP password")

say "Installing PHP $PHP, nginx and PostgreSQL"
export DEBIAN_FRONTEND=noninteractive
apt-get update -q
apt-get install -yq software-properties-common curl git unzip
add-apt-repository -y ppa:ondrej/php
apt-get update -q
apt-get install -yq nginx postgresql certbot python3-certbot-nginx \
  php$PHP-fpm php$PHP-cli php$PHP-pgsql php$PHP-intl php$PHP-mbstring php$PHP-xml \
  php$PHP-curl php$PHP-zip php$PHP-bcmath
command -v composer >/dev/null || curl -fsSL https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

say "Creating the database"
DB_PASSWORD=$(openssl rand -hex 24)
sudo -u postgres psql -q -c "CREATE USER openbka WITH PASSWORD '$DB_PASSWORD';"
sudo -u postgres psql -q -c "CREATE DATABASE openbka OWNER openbka;"

say "Downloading OpenBKA"
mkdir -p "$APP" && chown www-data:www-data "$APP"
sudo -u www-data git clone --quiet "$REPO" "$APP"
cd "$APP"
TAG=$(sudo -u www-data git describe --tags "$(sudo -u www-data git rev-list --tags --max-count=1)" 2>/dev/null || echo main)
sudo -u www-data git checkout --quiet "$TAG"
sudo -u www-data COMPOSER_HOME=/tmp/composer composer install --no-dev --optimize-autoloader --no-interaction

umask 077
cat > .env <<ENV
APP_NAME="OpenBKA"
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:$(openssl rand -base64 32)
APP_URL=https://$DOMAIN
OPENBKA_MODE=self-hosted

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=openbka
DB_USERNAME=openbka
DB_PASSWORD=$DB_PASSWORD

CACHE_STORE=database
QUEUE_CONNECTION=database
SESSION_DRIVER=database

MAIL_MAILER=smtp
MAIL_HOST=$SMTP_HOST
MAIL_PORT=$SMTP_PORT
MAIL_USERNAME=$SMTP_USER
MAIL_PASSWORD="$SMTP_PASS"
MAIL_FROM_ADDRESS=$EMAIL
MAIL_FROM_NAME="$ASSOCIATION"
ENV
chown www-data:www-data .env
umask 022

sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan optimize

say "Configuring nginx"
cat > /etc/nginx/sites-available/openbka <<NGINX
server {
    listen 80;
    server_name $DOMAIN;
    root $APP/public;
    index index.php;
    client_max_body_size 20m;

    location / { try_files \$uri \$uri/ /index.php?\$query_string; }
    location ~ \.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php$PHP-fpm.sock;
    }
    location ~ /\.(?!well-known) { deny all; }
}
NGINX
ln -sf /etc/nginx/sites-available/openbka /etc/nginx/sites-enabled/openbka
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx

say "Background jobs"
cat > /etc/systemd/system/openbka-queue.service <<UNIT
[Unit]
Description=OpenBKA queue worker
After=network.target postgresql.service

[Service]
User=www-data
Restart=always
ExecStart=/usr/bin/php $APP/artisan queue:work --tries=3 --max-time=3600

[Install]
WantedBy=multi-user.target
UNIT
systemctl daemon-reload
systemctl enable --now openbka-queue
echo "* * * * * www-data /usr/bin/php $APP/artisan schedule:run >/dev/null 2>&1" > /etc/cron.d/openbka

say "Getting an HTTPS certificate"
certbot --nginx -d "$DOMAIN" --non-interactive --agree-tos -m "$EMAIL" --redirect

sudo -u www-data php artisan openbka:install --association="$ASSOCIATION" --name="$NAME" --email="$EMAIL"

say "Done. OpenBKA is at https://$DOMAIN"
echo "Update later by running this script again."
