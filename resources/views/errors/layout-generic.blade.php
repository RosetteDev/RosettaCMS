<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, follow">
    <title>{{ $seo['title'] ?? 'Error' }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
               font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; background: #0f172a; color: #e2e8f0; padding: 1.5rem; }
        .card { text-align: center; max-width: 32rem; }
        .code { font-size: 0.85rem; letter-spacing: 0.2em; text-transform: uppercase; color: #94a3b8; }
        h1 { font-size: 2.25rem; margin: 0.5rem 0; }
        p { color: #94a3b8; line-height: 1.6; }
        a.btn { display: inline-block; margin-top: 1.25rem; padding: 0.65rem 1.4rem; border-radius: 6px;
                background: #38bdf8; color: #0f172a; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
    <div class="card">
        @yield('content')
    </div>
</body>
</html>
