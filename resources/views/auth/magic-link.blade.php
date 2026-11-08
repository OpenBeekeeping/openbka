<!doctype html>
<html lang="en-GB">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Sign in · {{ config('app.name') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('bickerton/brand/favicon.svg') }}">
    <link rel="stylesheet" href="{{ asset('bickerton/css/fonts.css') }}">
    @include('filament.head')
    <style>
        body { margin: 0; min-height: 100vh; display: grid; place-content: center; gap: var(--space-6); padding: var(--space-4); background: var(--surface-sunken); color: var(--text-primary); font-family: var(--font-body); font-size: var(--text-base); line-height: var(--leading-normal); }
        .ob-brand { justify-self: center; }
        .ob-card { width: min(400px, calc(100vw - 32px)); padding: var(--space-6); display: grid; gap: var(--space-3); border: var(--border-width) solid var(--border-subtle); }
        h1 { margin: 0; font-family: var(--font-display); font-weight: var(--fw-extrabold); font-size: var(--text-2xl); letter-spacing: var(--tracking-tight); line-height: var(--leading-snug); }
        p { margin: 0; color: var(--text-secondary); }
        form { margin: var(--space-2) 0 0; }
        .btn { display: flex; align-items: center; justify-content: center; width: 100%; height: 48px; margin-top: var(--space-2); border: 0; border-radius: var(--radius-pill); background: var(--brand); color: var(--on-brand); font: inherit; font-weight: var(--fw-bold); text-decoration: none; cursor: pointer; transition: background var(--dur-fast) var(--ease-out), transform var(--dur-fast) var(--ease-out); }
        .btn:hover { background: var(--wax-900); color: var(--brand); }
        .btn:active { transform: translateY(1px); }
    </style>
</head>
<body>
    @include('filament.honeycomb')
    @include('filament.brand')

    <main class="ob-card">
        @if ($valid)
            <h1>Sign in to {{ config('app.name') }}</h1>
            <p>Continue to finish signing in on this device.</p>
            <form method="post" action="{{ route('magic-link.store', ['token' => $token]) }}">
                @csrf
                <button type="submit" class="btn">Continue</button>
            </form>
        @else
            <h1>This link has expired</h1>
            <p>Sign-in links work once and expire after {{ config('openbka.magic_link_ttl') }} minutes. Ask for a new one and we’ll email it straight over.</p>
            <a class="btn" href="{{ filament()->getDefaultPanel()->getLoginUrl() }}">Get a new link</a>
        @endif
    </main>
</body>
</html>
