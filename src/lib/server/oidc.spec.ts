import { describe, expect, it, vi } from 'vitest';

vi.mock('$app/env/private', () => ({
	ORIGIN: 'https://bka.example',
	OIDC_ISSUER: 'https://accounts.example',
	OIDC_CLIENT_ID: 'id',
	OIDC_CLIENT_SECRET: 'secret'
}));

const { safeReturnTo } = await import('./oidc');

describe('safeReturnTo', () => {
	it.each(['/', '/members', '/members?page=2'])('allows same-site path %s', (path) => {
		expect(safeReturnTo(path)).toBe(path);
	});

	it.each([null, '', 'https://evil.example', '//evil.example', '/\\evil.example', 'members'])(
		'falls back to / for %s',
		(value) => {
			expect(safeReturnTo(value)).toBe('/');
		}
	);
});
