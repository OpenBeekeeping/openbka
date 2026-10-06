import { redirect } from '@sveltejs/kit';
import { OIDC_ISSUER } from '$app/env/private';
import {
	client,
	FLOW_COOKIE,
	getOidcConfig,
	REDIRECT_URI,
	SCOPE,
	safeReturnTo
} from '#lib/server/oidc.ts';

export const GET = async ({ cookies, url }) => {
	const config = await getOidcConfig();

	const flow = {
		state: client.randomState(),
		nonce: client.randomNonce(),
		verifier: client.randomPKCECodeVerifier(),
		returnTo: safeReturnTo(url.searchParams.get('returnTo'))
	};

	// Short-lived, readable only by the callback route.
	cookies.set(FLOW_COOKIE, JSON.stringify(flow), {
		path: '/auth/callback',
		httpOnly: true,
		secure: true,
		sameSite: 'lax',
		maxAge: 600
	});

	const authorizationUrl = client.buildAuthorizationUrl(config, {
		redirect_uri: REDIRECT_URI,
		scope: SCOPE,
		state: flow.state,
		nonce: flow.nonce,
		code_challenge: await client.calculatePKCECodeChallenge(flow.verifier),
		code_challenge_method: 'S256'
	});

	redirect(303, authorizationUrl.href, { external: [OIDC_ISSUER] });
};
