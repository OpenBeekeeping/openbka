<script lang="ts">
	let { data } = $props();
</script>

<svelte:head>
	<title>OpenBKA</title>
</svelte:head>

<h1>OpenBKA</h1>
<p>Open-source management for beekeeping associations.</p>

{#if data.user}
	<p>Signed in as <strong>{data.user.name || data.user.email}</strong></p>
	<form method="POST" action="/auth/sign-out">
		<button type="submit">Sign out</button>
	</form>
{:else}
	<p><a href="/auth/sign-in" data-sveltekit-reload>Sign in with your Open Beekeeping account</a></p>
{/if}
