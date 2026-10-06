<script lang="ts">
	import favicon from '#lib/assets/favicon.svg';
	import type { LayoutProps } from './$types';

	let { children }: LayoutProps = $props();
</script>

<svelte:head>
	<link rel="icon" href={favicon} />
</svelte:head>

{@render children()}

<style>
	/* app.html wraps the app in #app; keep it out of layout (no inline style, for the CSP). */
	:global(#app) {
		display: contents;
	}
</style>
