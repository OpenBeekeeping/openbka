<script lang="ts">
	let { data } = $props();
</script>

<svelte:head>
	<title>OpenBKA</title>
</svelte:head>

<section class="intro">
	<span class="ob-badge ob-badge--product">openbka</span>
	<h1 class="display">Run your beekeeping association</h1>
	<p class="lede">
		Membership and records for your BKA, in one place. The code is public, free to use and open to
		change.
	</p>
</section>

{#if data.user}
	<article class="ob-card">
		<span class="ob-card__kicker">signed in</span>
		<h2 class="ob-card__title">Welcome, {data.user.name || data.user.email}</h2>
		<p class="ob-card__body">Membership tools are on the way.</p>
	</article>
{:else}
	<article class="ob-card">
		<span class="ob-card__kicker">get started</span>
		<h2 class="ob-card__title">Sign in with your Open Beekeeping account</h2>
		<p class="ob-card__body">One account for every Open Beekeeping tool.</p>
		<div class="ob-card__actions">
			<a class="ob-btn ob-btn--primary ob-btn--lg" href="/auth/sign-in" data-sveltekit-reload>
				sign in
			</a>
		</div>
	</article>
{/if}

<style>
	.intro {
		display: flex;
		flex-direction: column;
		align-items: flex-start;
		gap: var(--space-4);
		margin-bottom: var(--space-8);
	}

	.intro h1 {
		margin: 0;
	}

	.lede {
		margin: 0;
		max-width: 40rem;
		color: var(--ink-muted);
	}
</style>
