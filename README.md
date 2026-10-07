# OpenBKA

Open-source, self-hostable management for beekeeping associations (BKAs).

Part of [Open Beekeeping](https://openbeekeeping.org). A managed, hosted version is available at [openbka.org](https://openbka.org).

> **Status:** early development, not yet ready for use.

## Stack

- [SvelteKit](https://svelte.dev/docs/kit) on [Cloudflare Workers](https://developers.cloudflare.com/workers/)
- [D1](https://developers.cloudflare.com/d1/) (SQLite) via [Drizzle ORM](https://orm.drizzle.team/)
- [R2](https://developers.cloudflare.com/r2/) for file storage

It's serverless by design: no servers to patch, no database exposed to the internet, and it runs within Cloudflare's free tier for most associations.

## Sign-in

People sign in with their Open Beekeeping account, provided by Guardbee (the Open Beekeeping sign-in service, not yet public), using OpenID Connect. OpenBKA has no passwords or login forms of its own; it stores each person's account ID, email and name, plus its own sessions.

Each instance is registered with Guardbee as an app, and is given a client ID and secret:

| Variable             | Value                                                      |
| -------------------- | ---------------------------------------------------------- |
| `ORIGIN`             | This instance's public URL, e.g. `https://bka.example.org` |
| `OIDC_ISSUER`        | Defaults to `https://accounts.openbeekeeping.org`          |
| `OIDC_CLIENT_ID`     | Issued when the instance is registered                     |
| `OIDC_CLIENT_SECRET` | Issued with the client ID                                  |

## Development

Requires Node 24 (`nvm use` picks it up from `.nvmrc`), and a local copy of Guardbee running on port 4173 (`npm run preview` there). Guardbee isn't public yet; ask a maintainer for access.

Register this app with your local Guardbee, which writes the client ID and secret into `.env`:

```sh
cd ../guardbee
npm run client:register -- --name "OpenBKA (local)" \
  --redirect http://localhost:5173/auth/callback --redirect http://localhost:4174/auth/callback \
  --post-logout http://localhost:5173/ --post-logout http://localhost:4174/ \
  --env-file ../openbka/.env
```

Then add `ORIGIN="http://localhost:5173"` and `OIDC_ISSUER="http://localhost:4173"` to `.env`, and copy it to `.dev.vars` with `ORIGIN="http://localhost:4174"` for `npm run preview`.

```sh
npm install
npm run db:migrate:local   # create the local D1 database
npm run dev
```

| Command                    | What it does                                                  |
| -------------------------- | ------------------------------------------------------------- |
| `npm run dev`              | Start the dev server                                          |
| `npm run check`            | Type-check                                                    |
| `npm run lint`             | Prettier + ESLint                                             |
| `npm test`                 | Unit tests                                                    |
| `npm run test:e2e`         | Sign-in end to end (needs both apps running in preview)       |
| `npm run build`            | Production build                                              |
| `npm run preview`          | Run the production build locally in the Workers runtime       |
| `npm run gen`              | Regenerate `worker-configuration.d.ts` after editing bindings |
| `npm run db:generate`      | Generate a migration from `src/lib/server/db/schema.ts`       |
| `npm run db:migrate:local` | Apply migrations to the local database                        |

### Dependency install scripts

npm only runs install scripts for packages approved in `allowScripts` in `package.json`. If an upgrade needs a new one, review it with `npm install-scripts ls` before approving.

## Self-hosting

You need a (free) Cloudflare account.

```sh
npx wrangler login
npm run setup       # once: create the D1 database in the EU jurisdiction
npx wrangler secret put ORIGIN
npx wrangler secret put OIDC_CLIENT_ID
npx wrangler secret put OIDC_CLIENT_SECRET
npm run deploy      # deploys, creating the R2 bucket (EU) on first run
npm run db:migrate  # apply migrations to the deployed database
```

Member data is stored in Cloudflare's EU jurisdiction, so it never leaves the EU. That is suitable for UK and EU associations under (UK) GDPR. To use another jurisdiction, change `jurisdiction` in `wrangler.jsonc` and the `setup` script before the first deploy.

## Licence

[AGPL-3.0-or-later](LICENSE). If you run a modified version as a service for others, you must make your source available to its users.
