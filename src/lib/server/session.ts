import { eq } from 'drizzle-orm';
import type { Cookies } from '@sveltejs/kit';
import { getDb } from '#lib/server/db/index.ts';
import { session, user } from '#lib/server/db/schema.ts';

export const SESSION_COOKIE = 'openbka_session';
const DAY = 24 * 60 * 60 * 1000;
const SESSION_LENGTH = 30 * DAY;
// Sessions in active use are extended once they're half way to expiry.
const RENEW_AFTER = SESSION_LENGTH / 2;

function base64url(bytes: Uint8Array) {
	return btoa(String.fromCharCode(...bytes))
		.replaceAll('+', '-')
		.replaceAll('/', '_')
		.replace(/=+$/, '');
}

async function hashToken(token: string) {
	const digest = await crypto.subtle.digest('SHA-256', new TextEncoder().encode(token));
	return base64url(new Uint8Array(digest));
}

function setCookie(cookies: Cookies, token: string, expiresAt: Date) {
	cookies.set(SESSION_COOKIE, token, {
		path: '/',
		httpOnly: true,
		secure: true,
		sameSite: 'lax',
		expires: expiresAt
	});
}

export async function createSession(
	d1: D1Database,
	cookies: Cookies,
	userId: string,
	idToken?: string
) {
	const token = base64url(crypto.getRandomValues(new Uint8Array(32)));
	const expiresAt = new Date(Date.now() + SESSION_LENGTH);
	await getDb(d1)
		.insert(session)
		.values({ id: await hashToken(token), userId, idToken, expiresAt });
	setCookie(cookies, token, expiresAt);
}

export async function validateSession(d1: D1Database, cookies: Cookies) {
	const token = cookies.get(SESSION_COOKIE);
	if (!token) return null;

	const db = getDb(d1);
	const id = await hashToken(token);
	const [row] = await db
		.select({ session, user })
		.from(session)
		.innerJoin(user, eq(session.userId, user.id))
		.where(eq(session.id, id));

	if (!row || row.session.expiresAt.getTime() <= Date.now()) {
		if (row) await db.delete(session).where(eq(session.id, id));
		cookies.delete(SESSION_COOKIE, { path: '/' });
		return null;
	}

	if (row.session.expiresAt.getTime() - Date.now() < RENEW_AFTER) {
		const expiresAt = new Date(Date.now() + SESSION_LENGTH);
		await db.update(session).set({ expiresAt }).where(eq(session.id, id));
		setCookie(cookies, token, expiresAt);
	}

	return row;
}

/** Deletes the current session and returns its ID token, for signing out of the accounts service. */
export async function deleteSession(d1: D1Database, cookies: Cookies) {
	const token = cookies.get(SESSION_COOKIE);
	cookies.delete(SESSION_COOKIE, { path: '/' });
	if (!token) return undefined;

	const [row] = await getDb(d1)
		.delete(session)
		.where(eq(session.id, await hashToken(token)))
		.returning({ idToken: session.idToken });
	return row?.idToken ?? undefined;
}
