import { defineConfig } from 'drizzle-kit';

// Drizzle only generates SQL migrations into ./drizzle; Wrangler applies them
// (`npm run db:migrate:local` / `npm run db:migrate`), so no API token is needed.
export default defineConfig({
	schema: './src/lib/server/db/schema.ts',
	out: './drizzle',
	dialect: 'sqlite',
	verbose: true,
	strict: true
});
