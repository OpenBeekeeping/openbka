// See https://svelte.dev/docs/kit/types#app.d.ts
// for information about these interfaces
// Cloudflare bindings come from `import { env } from 'cloudflare:workers'`.
declare global {
	namespace App {
		interface Locals {
			user?: { id: string; email: string; name: string | null };
		}

		// interface Error {}
		// interface PageData {}
		// interface PageState {}
	}
}

export {};
