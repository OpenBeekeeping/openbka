<!doctype html>
<html lang="en-GB" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Sign in · {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('design/logos/openbka-icon.svg') }}">
    <link rel="stylesheet" href="{{ asset('design/css/openbeekeeping.css') }}">
    <style>
        body { margin: 0; min-height: 100vh; display: grid; place-content: center; gap: var(--space-8); padding: var(--space-4); background: var(--surface); color: var(--ink); }
        .ob-lockup { font-size: 28px; justify-self: center; }
        .ob-card { width: min(380px, calc(100vw - 32px)); }
        form, .ob-card__actions { margin: 0; }
        .ob-btn { width: 100%; justify-content: center; }
    </style>
</head>
<body data-product="bka">
    <span class="ob-lockup" aria-label="openbka">
        <svg class="ob-mark" viewBox="0 0 32 32" aria-hidden="true">
            <polyline class="ob-mark__frame" points="9.75,5.17 3.5,16 9.75,26.83"/>
            <polyline class="ob-mark__frame" points="22.25,5.17 28.5,16 22.25,26.83"/>
            <path class="ob-mark__cell" d="M12.75 10.37 L19.25 10.37 L20.55 12.62 L11.45 12.62Z M10.15 14.87 L21.85 14.87 L22.5 16 L21.85 17.13 L10.15 17.13 L9.5 16Z M11.45 19.38 L20.55 19.38 L19.25 21.63 L12.75 21.63Z"/>
        </svg>
        <span>open<b>bka</b></span>
    </span>

    <main class="ob-card">
        @if ($valid)
            <h1 class="ob-card__title">Sign in to {{ config('app.name') }}</h1>
            <p class="ob-card__body">Continue to finish signing in on this device.</p>
            <form class="ob-card__actions" method="post" action="{{ route('magic-link.store', ['token' => $token]) }}">
                @csrf
                <button type="submit" class="ob-btn ob-btn--primary ob-btn--lg">continue</button>
            </form>
        @else
            <h1 class="ob-card__title">Error: this link has expired</h1>
            <p class="ob-card__body">Sign-in links work once and expire after {{ config('openbka.magic_link_ttl') }} minutes. Ask for a new one.</p>
            <div class="ob-card__actions">
                <a class="ob-btn ob-btn--primary ob-btn--lg" href="{{ filament()->getDefaultPanel()->getLoginUrl() }}">get a new link</a>
            </div>
        @endif
    </main>
</body>
</html>
