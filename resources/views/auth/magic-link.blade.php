<!doctype html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Sign in · {{ config('app.name') }}</title>
    <style>
        :root { color-scheme: dark; --bg:#0d0d0f; --fg:#f2f2f2; --muted:#9a9aa3; --line:#2a2a30; --accent:#ffd60a; }
        * { box-sizing: border-box; }
        body { margin:0; min-height:100vh; display:grid; place-items:center; padding:16px; background:var(--bg); color:var(--fg); font:16px/1.5 "IBM Plex Sans", system-ui, sans-serif; }
        main { width:100%; max-width:380px; border:1px solid var(--line); border-radius:12px; padding:32px; }
        h1 { font-size:1.25rem; margin:0 0 8px; }
        p { color:var(--muted); margin:0 0 24px; }
        button, a.button { display:block; width:100%; text-align:center; padding:12px; border:0; border-radius:8px; background:var(--accent); color:#111; font:inherit; font-weight:600; cursor:pointer; text-decoration:none; }
    </style>
</head>
<body>
<main>
    @if ($valid)
        <h1>Sign in to {{ config('app.name') }}</h1>
        <p>Continue to finish signing in on this device.</p>
        <form method="post" action="{{ route('magic-link.store', ['token' => $token]) }}">
            @csrf
            <button type="submit">continue</button>
        </form>
    @else
        <h1>Error: this link has expired</h1>
        <p>Sign-in links work once and expire after {{ config('openbka.magic_link_ttl') }} minutes. Ask for a new one.</p>
        <a class="button" href="{{ filament()->getDefaultPanel()->getLoginUrl() }}">get a new link</a>
    @endif
</main>
</body>
</html>
