<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? ($site->name ?? 'Store') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: system-ui, -apple-system, sans-serif; background: #f8fafc; color: #1f2937; line-height: 1.6; }
        .container { max-width: 1100px; margin: 0 auto; padding: 0 1rem; }
        .nav { background: #fff; border-bottom: 1px solid #e5e7eb; position: sticky; top: 0; z-index: 10; }
        .nav-inner { display: flex; align-items: center; justify-content: space-between; height: 60px; }
        .nav a { text-decoration: none; color: #374151; font-weight: 500; }
        .nav .brand { font-size: 1.25rem; font-weight: 700; color: #1f2937; }
        .nav-links { display: flex; align-items: center; gap: 1.25rem; }
        .btn { display: inline-block; padding: 0.55rem 1.25rem; border-radius: 8px; font-weight: 600; text-decoration: none; cursor: pointer; border: 1px solid transparent; font-size: 0.95rem; }
        .btn-primary { background: #6366f1; color: #fff; }
        .btn-primary:hover { background: #4f46e5; }
        .btn-outline { background: #fff; border-color: #d1d5db; color: #374151; }
        .btn-outline:hover { background: #f3f4f6; }
        .btn-sm { padding: 0.3rem 0.75rem; font-size: 0.85rem; }
        .btn-danger { background: #fee2e2; color: #b91c1c; border-color: #fecaca; }
        .btn-danger:hover { background: #fecaca; }
        .alert { padding: 0.9rem 1.1rem; border-radius: 8px; margin: 1rem 0; border: 1px solid; }
        .alert-success { background: #ecfdf5; border-color: #a7f3d0; color: #065f46; }
        .alert-danger { background: #fef2f2; border-color: #fecaca; color: #991b1b; }
        .page { padding: 2.5rem 0 4rem; }
        h1 { font-size: 1.9rem; margin-bottom: 0.5rem; }
        .muted { color: #6b7280; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 1.25rem; margin-top: 1.5rem; }
        .card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 1.25rem; display: flex; flex-direction: column; gap: 0.6rem; }
        .card h2 { font-size: 1.15rem; }
        .card .price { font-size: 1.25rem; font-weight: 700; color: #4338ca; }
        .card .price .was { font-size: 0.85rem; color: #6b7280; text-decoration: line-through; font-weight: 400; margin-left: 0.4rem; }
        .badge { display: inline-block; padding: 0.15rem 0.5rem; border-radius: 999px; font-size: 0.72rem; font-weight: 600; }
        .badge-sale { background: #fee2e2; color: #b91c1c; }
        .badge-out { background: #e5e7eb; color: #4b5563; }
        .badge-ok { background: #dcfce7; color: #166534; }
        .qty { display: flex; align-items: center; gap: 0.5rem; }
        .qty input { width: 4rem; padding: 0.35rem; border: 1px solid #d1d5db; border-radius: 6px; text-align: center; }
        table.line-items { width: 100%; border-collapse: collapse; background: #fff; border-radius: 12px; overflow: hidden; }
        table.line-items th, table.line-items td { padding: 0.9rem 1rem; border-bottom: 1px solid #e5e7eb; text-align: left; }
        table.line-items th { background: #f1f5f9; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; color: #475569; }
        .totals { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 1.25rem; margin-top: 1.25rem; }
        .totals .row { display: flex; justify-content: space-between; padding: 0.35rem 0; }
        .totals .total { font-size: 1.3rem; font-weight: 700; border-top: 1px solid #e5e7eb; margin-top: 0.5rem; padding-top: 0.75rem; }
        form.stack { display: flex; flex-direction: column; gap: 1rem; }
        label.field { display: flex; flex-direction: column; gap: 0.35rem; font-weight: 600; font-size: 0.9rem; }
        input.text, select.text, textarea.text { padding: 0.55rem 0.75rem; border: 1px solid #d1d5db; border-radius: 8px; font-size: 0.95rem; font-weight: 400; font-family: inherit; }
        input.text:focus, select.text:focus, textarea.text:focus { outline: 2px solid #6366f1; outline-offset: 0; border-color: #6366f1; }
        .error-text { color: #b91c1c; font-size: 0.85rem; margin-top: 0.25rem; }
        .center { text-align: center; }
        .section-title { font-size: 1.3rem; margin: 1.75rem 0 0.75rem; }
    </style>
</head>
<body>
    <nav class="nav">
        <div class="container nav-inner">
            <a href="{{ route('storefront.products') }}" class="brand">{{ $site->name ?? 'Store' }}</a>
            <div class="nav-links">
                <a href="{{ route('storefront.products') }}">Products</a>
                <a href="{{ route('storefront.cart') }}">Cart{{ $cartCount > 0 ? ' (' . $cartCount . ')' : '' }}</a>
                @auth
                    <a href="{{ url('/dashboard') }}">Dashboard</a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="container page">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @yield('content')
    </main>
</body>
</html>
