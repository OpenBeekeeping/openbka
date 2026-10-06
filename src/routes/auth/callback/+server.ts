import { error, redirect } from '@sveltejs/kit';
import { env } from 'cloudflare:workers';
import { getDb } from '#lib/server/db/index.ts';
import { user } from '#lib/server/db/schema.ts';
import { client, FLOW_COOKIE, getOidcConfig, safeReturnTo } from '#lib/server/oidc.ts';
import { createSession } from '#lib/server/session.ts';

type Flow = { state: string; nonce: string; verifier: string; returnTo: string };

export const GET = async ({ cookies, url }) => {
	const raw = cookies.get(FLOW_COOKIE);
	cookies.delete(FLOW_COOKIE, { path: '/auth/callback' });

	// The user cancelled on the consent screen (or the request was refused).
	if (url.searchParams.get('error') === 'access_denied') redirect(303, '/');
	if (!raw) error(400, 'Sign-in expired. Please try again.');

	const flow = JSON.parse(raw) as Flow;
	const config = await getOidcConfig();

	// Checks state, PKCE, nonce, issuer and the ID token's claims.
	const tokens = await client
		.authorizationCodeGrant(config, url, {
			expectedState: flow.state,
			expectedNonce: flow.nonce,
			pkceCodeVerifier: flow.verifier,
			idTokenExpected: true
		})
		.catch((cause) => {
			console.error('OIDC code exchange failed', cause);
			error(400, 'Sign-in failed. Please try again.');
		});

	const { sub } = tokens.claims()!;
	const info = await client.fetchUserInfo(config, tokens.access_token, sub);
	if (!info.email || info.email_verified !== true)
		error(400, 'Your account needs a verified email.');

	const [account] = await getDb(env.DB)
		.insert(user)
		.values({ sub, email: info.email, name: info.name || null })
		.onConflictDoUpdate({
			target: user.sub,
			set: { email: info.email, name: info.name || null, updatedAt: new Date() }
		})
		.returning({ id: user.id });

	await createSession(env.DB, cookies, account.id, tokens.id_token);
	redirect(303, safeReturnTo(flow.returnTo));
};
