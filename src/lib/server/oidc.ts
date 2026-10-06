import * as client from 'openid-client';
import { OIDC_CLIENT_ID, OIDC_CLIENT_SECRET, OIDC_ISSUER, ORIGIN } from '$app/env/private';

export const REDIRECT_URI = `${ORIGIN}/auth/callback`;
export const SCOPE = 'openid profile email';
// Holds state, nonce and PKCE verifier between /auth/sign-in and /auth/callback.
export const FLOW_COOKIE = 'openbka_oidc';

let config: Promise<client.Configuration> | undefined;

/** Discovery result for the accounts service, cached for the life of the isolate. */
export function getOidcConfig() {
	config ??= client
		.discovery(
			new URL(OIDC_ISSUER),
			OIDC_CLIENT_ID,
			undefined,
			client.ClientSecretBasic(OIDC_CLIENT_SECRET),
			// Plain HTTP is only allowed for a local accounts service in development.
			new URL(OIDC_ISSUER).hostname === 'localhost'
				? { execute: [client.allowInsecureRequests] }
				: undefined
		)
		.catch((error) => {
			config = undefined;
			throw error;
		});
	return config;
}

/** Only same-site paths, so the sign-in flow can't be used as an open redirect. */
export function safeReturnTo(value: string | null) {
	return value && value.startsWith('/') && !value.startsWith('//') && !value.startsWith('/\\')
		? value
		: '/';
}

export { client };
