import { sequence, type Handle } from '@sveltejs/kit/hooks';
import { env } from 'cloudflare:workers';
import { validateSession } from '#lib/server/session.ts';

// Security headers not covered by Kit's CSP config (see vite.config.ts).
const securityHeaders = {
	'Strict-Transport-Security': 'max-age=63072000; includeSubDomains',
	'X-Content-Type-Options': 'nosniff',
	'Referrer-Policy': 'strict-origin-when-cross-origin',
	'Permissions-Policy': 'camera=(), microphone=(), geolocation=(), payment=()',
	'Cross-Origin-Opener-Policy': 'same-origin'
};

const handleSecurityHeaders: Handle = async ({ event, resolve }) => {
	const response = await resolve(event);
	for (const [name, value] of Object.entries(securityHeaders)) {
		response.headers.set(name, value);
	}
	return response;
};

const handleSession: Handle = async ({ event, resolve }) => {
	const current = await validateSession(env.DB, event.cookies);
	if (current) {
		event.locals.user = {
			id: current.user.id,
			email: current.user.email,
			name: current.user.name
		};
	}
	return resolve(event);
};

export const handle = sequence(handleSecurityHeaders, handleSession);
