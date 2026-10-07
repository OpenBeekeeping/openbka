<script lang="ts">
	import '@openbeekeeping/design/openbeekeeping.css';
	import icon from '@openbeekeeping/design/logos/openbka-icon.svg';
	import Lockup from '#lib/components/Lockup.svelte';
	import type { LayoutProps } from './$types';

	let { children, data }: LayoutProps = $props();
</script>

<svelte:head>
	<link rel="icon" href={icon} type="image/svg+xml" />
</svelte:head>

<header class="ob-header">
	<Lockup name="OpenBKA" />
	<nav class="ob-header__nav" aria-label="main">
		{#if data.user}
			<span class="small signed-in">{data.user.name || data.user.email}</span>
			<form method="POST" action="/auth/sign-out">
				<button type="submit" class="ob-btn">sign out</button>
			</form>
		{:else}
			<a class="ob-btn" href="/auth/sign-in" data-sveltekit-reload>sign in</a>
		{/if}
	</nav>
</header>

<main>
	{@render children()}
</main>

<footer class="small">
	<span>Part of <a class="ob-link" href="https://openbeekeeping.org">Open Beekeeping</a></span>
	<span aria-hidden="true">·</span>
	<a class="ob-link" href="https://github.com/OpenBeekeeping/openbka">get the code</a>
</footer>

<style>
	/* app.html wraps the app in #app; keep it out of layout (no inline style, for the CSP). */
	:global(#app) {
		display: contents;
	}

	:global(body) {
		margin: 0;
		min-height: 100vh;
		display: flex;
		flex-direction: column;
		background: var(--surface);
		color: var(--ink);
		font-family: var(--font-sans);
		font-size: 15px;
		line-height: 24px;
		-webkit-font-smoothing: antialiased;
	}

	main {
		box-sizing: border-box;
		width: 100%;
		max-width: 60rem;
		margin: 0 auto;
		padding: var(--space-12) var(--space-4) var(--space-8);
		flex: 1;
	}

	footer {
		display: flex;
		flex-wrap: wrap;
		gap: var(--space-2);
		justify-content: center;
		padding: var(--space-6) var(--space-4);
		border-top: 1px solid var(--line);
		color: var(--ink-muted);
	}

	.signed-in {
		color: var(--ink-muted);
	}

	form {
		margin: 0;
	}
</style>
