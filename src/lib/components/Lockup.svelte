<script lang="ts">
	// The Open Beekeeping lockup: brackets + striped cell, then "open" + bold noun.
	// See @openbeekeeping/design docs/components.md (Logo).
	let { name, href = '/' }: { name: string; href?: string } = $props();

	// "Open Beekeeping" -> open + beekeeping; other names are shown whole, in bold.
	const parts = $derived.by(() => {
		const match = name.match(/^open\s*(.+)$/i);
		return match
			? { open: 'open', noun: match[1].replace(/\s+/g, '').toLowerCase() }
			: { open: '', noun: name };
	});
</script>

<a class="ob-lockup" {href} aria-label="{name} home">
	<svg class="ob-mark" viewBox="0 0 32 32" aria-hidden="true">
		<polyline class="ob-mark__frame" points="9.75,5.17 3.5,16 9.75,26.83" />
		<polyline class="ob-mark__frame" points="22.25,5.17 28.5,16 22.25,26.83" />
		<path
			class="ob-mark__cell"
			d="M12.75 10.37 L19.25 10.37 L20.55 12.62 L11.45 12.62Z M10.15 14.87 L21.85 14.87 L22.5 16 L21.85 17.13 L10.15 17.13 L9.5 16Z M11.45 19.38 L20.55 19.38 L19.25 21.63 L12.75 21.63Z"
		/>
	</svg>
	<span>{parts.open}<b>{parts.noun}</b></span>
</a>
