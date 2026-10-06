import { redirect } from '@sveltejs/kit';
import { env } from 'cloudflare:workers';
import { OIDC_ISSUER, ORIGIN } from '$app/env/private';
import { client, getOidcConfig } from '#lib/server/oidc.ts';
import { deleteSession } from '#lib/server/session.ts';

// POST only, so a link or image on another site can't sign people out.
export const POST = async ({ cookies }) => {
	const idToken = await deleteSession(env.DB, cookies);
	if (!idToken) redirect(303, '/');

	// Also end the session at the accounts service, then come back here.
	const config = await getOidcConfig();
	const endSession = client.buildEndSessionUrl(config, {
		id_token_hint: idToken,
		post_logout_redirect_uri: `${ORIGIN}/`
	});
	redirect(303, endSession.href, { external: [OIDC_ISSUER] });
};
