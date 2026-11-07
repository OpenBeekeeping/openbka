# OpenBKA

Open-source membership management for beekeeping associations (BKAs).

An **association** has **branches**, and each branch has **members**. Association admins see everything; branch admins see only their own branch.

- **Hosted:** sign up at [openbka.org](https://openbka.org). We run it, back it up and keep it updated.
- **Self-hosted:** run it on your own server for free (see below).

Built with Laravel 13 and Filament 5. Licensed under AGPL-3.0-or-later.

## Self-hosting

You need a Linux server with a domain name pointing at it (an A record), and an SMTP account for sending sign-in emails.

### With Docker (recommended)

```sh
curl -fsSL https://raw.githubusercontent.com/OpenBeekeeping/openbka/main/deploy/install.sh | sudo bash
```

The script installs Docker if needed, asks for your domain, association and email settings, then starts OpenBKA with PostgreSQL and automatic HTTPS (Caddy). Everything lives in `/opt/openbka`. Run the script again to update.

### Without Docker (Ubuntu 24.04+)

```sh
curl -fsSL https://raw.githubusercontent.com/OpenBeekeeping/openbka/main/deploy/install-ubuntu.sh | sudo bash
```

Installs PHP 8.4, nginx, PostgreSQL and a Let's Encrypt certificate directly on the server. Run it again to update to the latest release.

### Signing in

There are no passwords. You sign in with a one-time link sent by email, or with a passkey (add one from your profile). If email isn't working yet, print a link on the server:

```sh
docker compose exec app php artisan openbka:login you@example.com   # Docker
sudo -u www-data php artisan openbka:login you@example.com          # Ubuntu
```

## Modes

`OPENBKA_MODE` decides how the app behaves:

| | `self-hosted` (default) | `hosted` |
|---|---|---|
| Associations | One, created by `openbka:install` | Any number; people sign up at `/app/register` |
| Billing | None | Stripe, per association, after a free trial |

Hosted mode also needs `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET` and `OPENBKA_STRIPE_PRICE`. Point a Stripe webhook at `/stripe/webhook`.

## Development

Requires PHP 8.4 and Composer ([Laravel Herd](https://herd.laravel.com) works well).

```sh
composer install
cp .env.example .env && php artisan key:generate
touch database/database.sqlite && php artisan migrate
php artisan openbka:install
composer run dev
```

Run the tests with `php artisan test` and format with `vendor/bin/pint`.

### Design system

OpenBKA uses the [Open Beekeeping design system](https://github.com/OpenBeekeeping/design). Its tokens, fonts and logos are copied into `public/design` and `resources/design`. To update them, check out the design repo next to this one and run:

```sh
php artisan openbka:sync-design ../design
```

Filament's colours are built from the tokens in `App\Support\Design`, and `public/css/openbka.css` applies the brand rules (square corners, mono lowercase labels, yellow primary actions) to Filament's components.

## Security

Report vulnerabilities privately through GitHub's "Report a vulnerability" button on this repository.
