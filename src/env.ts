import { defineEnvVars } from '@sveltejs/kit/env';

const url = (name: string) => (value: string | undefined) => {
	if (!value) throw new Error(`${name} must be set`);
	return new URL(value).origin;
};

export const variables = defineEnvVars({
	ORIGIN: {
		description: 'Public origin of this OpenBKA instance, e.g. `https://sevenoaks.openbka.org`.',
		schema: url('ORIGIN')
	},
	OIDC_ISSUER: {
		description:
			'Open Beekeeping accounts service. Defaults to `https://accounts.openbeekeeping.org`.',
		schema: (value) => new URL(value || 'https://accounts.openbeekeeping.org').origin
	},
	OIDC_CLIENT_ID: {
		description: 'Client ID issued when this instance was registered with the accounts service.',
		schema: (value) => {
			if (!value) throw new Error('OIDC_CLIENT_ID must be set');
			return value;
		}
	},
	OIDC_CLIENT_SECRET: {
		description: 'Client secret issued with OIDC_CLIENT_ID.',
		schema: (value) => {
			if (!value) throw new Error('OIDC_CLIENT_SECRET must be set');
			return value;
		}
	}
});
