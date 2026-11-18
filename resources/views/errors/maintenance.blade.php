<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Maintenance &middot; {{ $siteName }}</title>
    <style>
        :root { color-scheme: light dark; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: #f3f4f6;
            color: #1f2937;
        }
        .card {
            width: 100%;
            max-width: 34rem;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.12);
            padding: 2.5rem;
            text-align: center;
        }
        .badge {
            display: inline-block;
            padding: 0.35rem 0.9rem;
            border-radius: 999px;
            background: #fef3c7;
            color: #92400e;
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        h1 { font-size: 1.75rem; margin: 1.25rem 0 0.75rem; }
        p { margin: 0 0 0.75rem; color: #4b5563; line-height: 1.6; }
        .actions { margin-top: 1.75rem; display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap; }
        a {
            display: inline-block;
            padding: 0.65rem 1.25rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            background: #3b82f6;
            color: #fff;
        }
        a.secondary { background: transparent; color: #3b82f6; border: 1px solid #bfdbfe; }
        a:hover { opacity: 0.9; }
    </style>
</head>
<body>
    <main class="card">
        <span class="badge">Maintenance</span>
        <h1>We&rsquo;ll be back shortly</h1>
        <p>{{ $siteName }} is temporarily offline for scheduled maintenance.</p>
        <p>Thanks for your patience. Please check back in a few minutes.</p>
        <div class="actions">
            <a href="{{ url('/') }}">Try again</a>
            <a class="secondary" href="{{ route('login') }}">Staff login</a>
        </div>
    </main>
</body>
</html>
