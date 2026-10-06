// End-to-end sign-in test: OpenBKA (npm run preview, :4174) signing in through a
// local accounts service (:4173, EMAIL_TRANSPORT="console", log at ACCOUNTS_LOG).
//
// Usage: npm run test:e2e
import { readFileSync } from 'node:fs';

const BKA = 'http://localhost:4174';
const ACCOUNTS = 'http://localhost:4173';
const ACCOUNTS_LOG = process.env.ACCOUNTS_LOG ?? '/tmp/acc_dev.log';
const EMAIL = `bka-e2e-${Date.now()}@example.org`;

// One cookie jar per site (path and Secure are ignored; fine for localhost tests).
const jars = new Map();
async function req(url, init = {}) {
	const { origin } = new URL(url);
	const jar = jars.get(origin) ?? new Map();
	jars.set(origin, jar);
	const headers = new Headers(init.headers);
	headers.set('cookie', [...jar].map(([k, v]) => `${k}=${v}`).join('; '));
	if (!init.method || init.method === 'GET') headers.set('accept', 'text/html');
	const res = await fetch(url, { ...init, headers, redirect: 'manual' });
	for (const c of res.headers.getSetCookie()) {
		const [pair, ...attrs] = c.split(';');
		const i = pair.indexOf('=');
		const name = pair.slice(0, i);
		const expired = attrs.some((a) => /max-age=0|expires=thu, 01 jan 1970/i.test(a.trim()));
		if (expired) jar.delete(name);
		else jar.set(name, pair.slice(i + 1));
	}
	// Better Auth answers fetch-mode requests with {redirect, url} instead of a 302.
	if (res.status === 200 && res.headers.get('content-type')?.includes('json')) {
		const body = await res
			.clone()
			.json()
			.catch(() => null);
		if (body?.redirect && body.url) return redirectTo(new URL(body.url, url).href);
	}
	return res;
}
const redirectTo = (location) => new Response(null, { status: 302, headers: { location } });
const next = (res, base) => new URL(res.headers.get('location'), base).href;
const step = (msg) => console.log(`✓ ${msg}`);
const fail = (msg) => {
	console.error(`✗ ${msg}`);
	process.exit(1);
};

// 1. Signed out to start with.
let html = await (await req(`${BKA}/`)).text();
if (!html.includes('Sign in with your Open Beekeeping account')) fail('home page not signed out');
step('OpenBKA home page offers sign-in');

// 2. Start sign-in: OpenBKA redirects to the accounts service, which asks us to sign in.
let res = await req(`${BKA}/auth/sign-in?returnTo=/%3Fwelcome`);
let url = next(res, BKA);
if (!url.startsWith(`${ACCOUNTS}/api/auth/oauth2/authorize?`)) fail(`sign-in -> ${url}`);
const authorize = new URL(url);
for (const p of ['state', 'nonce', 'code_challenge'])
	if (!authorize.searchParams.get(p)) fail(`authorize missing ${p}`);
step('OpenBKA redirects to accounts with state, nonce and PKCE');

res = await req(url);
url = next(res, ACCOUNTS);
if (!url.includes('/sign-in?')) fail(`authorize -> ${url}`);
html = await (await req(url)).text();
if (!html.includes('OpenBKA (local)')) fail('accounts sign-in page does not name OpenBKA');
step('accounts sign-in page says "to continue to OpenBKA (local)"');

// 3. Email sign-in (link read from the accounts log), resuming the authorization.
const params = new URL(url).searchParams;
for (const k of [...params.keys()])
	if (['sig', 'exp', 'prompt'].includes(k) || k.startsWith('ba_')) params.delete(k);
const logStart = readFileSync(ACCOUNTS_LOG, 'utf8').length;
res = await req(`${ACCOUNTS}/api/auth/sign-in/magic-link`, {
	method: 'POST',
	headers: { 'content-type': 'application/json', origin: ACCOUNTS },
	body: JSON.stringify({ email: EMAIL, callbackURL: `/api/auth/oauth2/authorize?${params}` })
});
if (!res.ok) fail(`magic link request ${res.status} ${await res.text()}`);
await new Promise((r) => setTimeout(r, 500));
const link = readFileSync(ACCOUNTS_LOG, 'utf8')
	.slice(logStart)
	.match(/http:\/\/localhost:4173\/api\/auth\/magic-link\/verify\S+/)?.[0];
if (!link) fail('no magic link in accounts log');

url = link;
for (let hops = 0; hops < 6 && !url.includes('/profile'); hops++)
	url = next(await req(url), ACCOUNTS);
if (!url.includes('/profile?')) fail(`after magic link -> ${url}`);
step('magic link signs in; new account is asked to finish its profile');

// 3b. Complete the profile; Guardbee continues the sign-in to consent.
const profileUrl = new URL(url);
res = await req(profileUrl.href, {
	method: 'POST',
	headers: {
		'content-type': 'application/x-www-form-urlencoded',
		origin: ACCOUNTS,
		accept: 'text/html'
	},
	body: new URLSearchParams([['name', 'Ada Apis']])
});
url = next(res, ACCOUNTS);
for (let hops = 0; hops < 6 && !url.includes('/consent'); hops++)
	url = next(await req(url), ACCOUNTS);
if (!url.includes('/consent')) fail(`after profile -> ${url}`);
step('profile saved; sign-in continues to the consent screen');

// 4. Approve; accounts redirects back to OpenBKA's callback with a code.
const consentUrl = new URL(url);
res = await req(`${ACCOUNTS}/api/auth/oauth2/consent`, {
	method: 'POST',
	headers: { 'content-type': 'application/json', origin: ACCOUNTS },
	body: JSON.stringify({
		accept: true,
		scope: 'openid profile email',
		oauth_query: consentUrl.search.slice(1)
	})
});
url = next(res, ACCOUNTS);
if (!url.startsWith(`${BKA}/auth/callback?`)) fail(`consent -> ${url}`);
step('consent returns to OpenBKA /auth/callback');

// 5. A callback with the wrong state is rejected (before trying the real one).
const tampered = new URL(url);
tampered.searchParams.set('state', 'wrong');
const savedJar = new Map(jars.get(BKA));
res = await req(tampered.href);
if (res.status !== 400) fail(`tampered state -> ${res.status}`);
jars.set(BKA, savedJar);
step('callback with tampered state rejected (400)');

// 6. The real callback signs in to OpenBKA and honours returnTo.
res = await req(url);
if (res.status !== 303 || next(res, BKA) !== `${BKA}/?welcome`)
	fail(`callback -> ${res.status} ${res.headers.get('location')}`);
if (!jars.get(BKA).has('openbka_session')) fail('no session cookie');
html = await (await req(`${BKA}/`)).text();
if (!html.includes('Ada Apis')) fail('home page does not show the signed-in user');
step(`signed in to OpenBKA as Ada Apis (${EMAIL})`);

// 7. The authorization code can't be replayed.
const replayJar = new Map(jars.get(BKA));
res = await req(url);
if (res.status !== 400) fail(`replayed callback -> ${res.status}`);
jars.set(BKA, replayJar);
step('replayed callback rejected (400)');

// 8. Cross-site sign-out is refused; same-site sign-out ends both sessions.
res = await req(`${BKA}/auth/sign-out`, {
	method: 'POST',
	headers: { 'content-type': 'application/x-www-form-urlencoded', origin: 'https://evil.example' }
});
if (res.status === 303) fail('cross-site sign-out was accepted');

res = await req(`${BKA}/auth/sign-out`, {
	method: 'POST',
	headers: { 'content-type': 'application/x-www-form-urlencoded', origin: BKA }
});
url = next(res, BKA);
if (!url.startsWith(`${ACCOUNTS}/api/auth/oauth2/end-session?`)) fail(`sign-out -> ${url}`);
res = await req(url);
url = next(res, ACCOUNTS);
if (url !== `${BKA}/`) fail(`end-session -> ${res.status} ${url}`);
html = await (await req(`${BKA}/`)).text();
if (!html.includes('Sign in with your Open Beekeeping account')) fail('still signed in to OpenBKA');
res = await req(`${ACCOUNTS}/account`);
if (!next(res, ACCOUNTS).endsWith('/sign-in')) fail('still signed in to accounts');
step('sign-out ends the OpenBKA and accounts sessions and returns home');

console.log('\nAll checks passed.');
