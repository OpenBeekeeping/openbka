@php
    $panel = filament()->getPanel('app');
    $trialDays = config('openbka.billing.trial_days');
@endphp
<!doctype html>
<html lang="en-GB">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>OpenBKA · Membership records for beekeeping associations</title>
    <meta name="description" content="OpenBKA keeps your beekeeping association's members, branches and renewals in one place. Open source, hosted in the UK or on your own server.">
    <link rel="icon" type="image/svg+xml" href="{{ asset('bickerton/brand/favicon.svg') }}">
    <link rel="stylesheet" href="{{ asset('bickerton/css/fonts.css') }}">
    @include('filament.head')
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: var(--surface-sunken); color: var(--text-primary); font-family: var(--font-body); font-size: var(--text-base); line-height: var(--leading-normal); }
        a { color: inherit; }
        .wrap { width: min(1080px, 100% - 32px); margin-inline: auto; }

        header.top { display: flex; align-items: center; justify-content: space-between; padding-block: var(--space-5); }
        header.top a.ob-brand { text-decoration: none; }
        .signin { font-weight: var(--fw-semibold); text-decoration: none; padding: var(--space-2) var(--space-4); border-radius: var(--radius-pill); }
        .signin:hover { background: var(--wax-100); }
        .top-links { display: flex; align-items: center; gap: var(--space-2); }
        .github { display: inline-flex; padding: var(--space-2); border-radius: var(--radius-pill); color: var(--text-primary); }
        .github:hover { background: var(--wax-100); }

        .hero { padding-block: var(--space-16) var(--space-20); max-width: 46rem; }
        .eyebrow { margin: 0 0 var(--space-4); font-size: var(--text-xs); font-weight: var(--fw-bold); letter-spacing: var(--tracking-eyebrow); text-transform: uppercase; color: var(--honey-800); }
        h1 { margin: 0; font-family: var(--font-display); font-weight: var(--fw-extrabold); font-size: clamp(2.25rem, 6vw, var(--text-6xl)); line-height: var(--leading-tight); letter-spacing: var(--tracking-tighter); text-wrap: balance; }
        .lede { margin: var(--space-6) 0 0; font-size: var(--text-lg); color: var(--text-secondary); max-width: 38rem; }
        .actions { display: flex; flex-wrap: wrap; align-items: center; gap: var(--space-3); margin-top: var(--space-8); }
        .btn { display: inline-flex; align-items: center; justify-content: center; height: 48px; padding-inline: var(--space-6); border-radius: var(--radius-pill); font-weight: var(--fw-bold); text-decoration: none; transition: background var(--dur-fast) var(--ease-out), color var(--dur-fast) var(--ease-out); }
        .btn-primary { background: var(--brand); color: var(--on-brand); }
        .btn-primary:hover { background: var(--wax-900); color: var(--brand); }
        .btn-quiet { border: var(--border-width) solid var(--border-default); background: var(--surface-card); }
        .btn-quiet:hover { background: var(--wax-100); }
        .note { margin: var(--space-3) 0 0; font-size: var(--text-sm); color: var(--text-muted); }

        h2 { margin: 0 0 var(--space-6); font-family: var(--font-display); font-weight: var(--fw-extrabold); font-size: var(--text-3xl); letter-spacing: var(--tracking-tight); line-height: var(--leading-snug); }
        section { padding-block: var(--space-12); }
        .grid { display: grid; gap: var(--space-4); grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); }
        .card { background: var(--surface-card); border: var(--border-width) solid var(--border-subtle); border-radius: var(--radius-lg); box-shadow: var(--shadow-xs); padding: var(--space-6); }
        .card h3 { margin: 0 0 var(--space-2); font-size: var(--text-lg); font-weight: var(--fw-bold); }
        .card p { margin: 0; color: var(--text-secondary); }
        .card ul { margin: var(--space-4) 0 0; padding-left: 1.2em; color: var(--text-secondary); }
        .card li + li { margin-top: var(--space-1); }
        .card .btn { margin-top: var(--space-6); }
        .plans { grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); }
        .plans .card:first-child { border-color: var(--honey-300); background: var(--surface-brand-subtle); }

        footer { padding-block: var(--space-12) var(--space-16); color: var(--text-muted); font-size: var(--text-sm); border-top: var(--border-width) solid var(--border-subtle); margin-top: var(--space-12); }
        footer a { color: var(--text-link); }
    </style>
</head>
<body>
    @include('filament.honeycomb')
    <div class="wrap">
        <header class="top">
            <a href="{{ url('/') }}">@include('filament.brand')</a>
            <nav class="top-links">
                <a class="signin" href="{{ $panel->getLoginUrl() }}">Sign in</a>
                <a class="github" href="https://github.com/OpenBeekeeping/openbka" aria-label="OpenBKA on GitHub" title="OpenBKA on GitHub">
                    <svg viewBox="0 0 16 16" width="22" height="22" aria-hidden="true" fill="currentColor"><path d="M8 0c4.42 0 8 3.58 8 8a8.013 8.013 0 0 1-5.45 7.59c-.4.08-.55-.17-.55-.38 0-.27.01-1.13.01-2.2 0-.75-.25-1.23-.54-1.48 1.78-.2 3.65-.88 3.65-3.95 0-.88-.31-1.59-.82-2.15.08-.2.36-1.02-.08-2.12 0 0-.67-.22-2.2.82-.64-.18-1.32-.27-2-.27-.68 0-1.36.09-2 .27-1.53-1.03-2.2-.82-2.2-.82-.44 1.1-.16 1.92-.08 2.12-.51.56-.82 1.28-.82 2.15 0 3.06 1.86 3.75 3.64 3.95-.23.2-.44.55-.51 1.07-.46.21-1.61.55-2.33-.66-.15-.24-.6-.83-1.23-.82-.67.01-.27.38.01.53.34.19.73.9.82 1.13.16.45.68 1.31 2.69.94 0 .67.01 1.3.01 1.49 0 .21-.15.45-.55.38A7.995 7.995 0 0 1 0 8c0-4.42 3.58-8 8-8Z"/></svg>
                </a>
            </nav>
        </header>

        <main>
            <div class="hero">
                <p class="eyebrow">For beekeeping associations</p>
                <h1>Your members, branches and renewals in one place</h1>
                <p class="lede">OpenBKA is a simple membership system for beekeeping associations. See who's due to renew, keep contact details up to date, and let each branch look after its own members.</p>
                <div class="actions">
                    <a class="btn btn-primary" href="{{ url('/app/register') }}">Start a free trial</a>
                    <a class="btn btn-quiet" href="{{ $panel->getLoginUrl() }}">Sign in</a>
                </div>
                <p class="note">{{ $trialDays }} days free. No card needed to start.</p>
            </div>

            <section aria-labelledby="features">
                <h2 id="features">What it does</h2>
                <div class="grid">
                    <div class="card">
                        <h3>Member records</h3>
                        <p>Names, contact details, membership numbers and notes, with each member marked active, lapsed or resigned.</p>
                    </div>
                    <div class="card">
                        <h3>Renewals</h3>
                        <p>The dashboard shows who renews in the next 30 days and who is overdue, so nobody slips through.</p>
                    </div>
                    <div class="card">
                        <h3>Branches</h3>
                        <p>Split your association into branches. Branch admins see and manage only their own branch's members.</p>
                    </div>
                    <div class="card">
                        <h3>No passwords</h3>
                        <p>Admins sign in with a link sent to their email, or with a passkey on their phone or laptop.</p>
                    </div>
                </div>
            </section>

            <section aria-labelledby="plans">
                <h2 id="plans">Two ways to use it</h2>
                <div class="grid plans">
                    <div class="card">
                        <h3>Hosted by us</h3>
                        <p>We run it for you, so there's nothing to install or update.</p>
                        <ul>
                            <li>Hosted in the UK</li>
                            <li>Backups and updates included</li>
                            <li>{{ $trialDays }}-day free trial</li>
                        </ul>
                        <a class="btn btn-primary" href="{{ url('/app/register') }}">Start a free trial</a>
                    </div>
                    <div class="card">
                        <h3>Run it yourself</h3>
                        <p>OpenBKA is open source. Install it on your own server for free.</p>
                        <ul>
                            <li>One command with Docker, or a script for Ubuntu</li>
                            <li>Your data stays on your server</li>
                            <li>AGPL-3.0 licence</li>
                        </ul>
                        <a class="btn btn-quiet" href="https://github.com/OpenBeekeeping/openbka#self-hosting">Installation guide</a>
                    </div>
                </div>
            </section>
        </main>

        <footer>
            <p>OpenBKA is open source and part of Open Beekeeping. <a href="https://github.com/OpenBeekeeping/openbka">Source code on GitHub</a>.</p>
        </footer>
    </div>
</body>
</html>
