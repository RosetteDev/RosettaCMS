@php
    $theme_slug = $theme_slug ?? ($site->theme ?? 'business');
    $theme_options = $theme_options ?? [];
    $palette = array_merge([
        'primary_color' => '#1e3a8a',
        'secondary_color' => '#64748b',
        'accent_color' => '#d97706',
        'bg_color' => '#ffffff',
        'text_color' => '#1f2937',
        'border_color' => '#e5e7eb',
        'font_family' => "system-ui, -apple-system, 'Segoe UI', sans-serif",
        'container_width' => '1140px',
        'body_font_size' => '16px',
        'line_height' => '1.65',
        'block_gap' => '2rem',
        'section_padding' => '4rem',
    ], array_filter($theme_options, fn ($value) => $value !== null && $value !== ''));
@endphp
@php
    $activeLanguage = $activeLanguage ?? (request()->attributes->get('activeLanguage') ?? \App\Models\Language::defaultLanguage());
    $activeLanguageCode = $activeLanguage?->code ?? app()->getLocale();
    $activeDirection = $activeLanguage?->isRtl() ? 'rtl' : 'ltr';
    $availableLanguages = \App\Models\Language::where('is_active', true)->orderBy('sort_order')->get();
@endphp
<!DOCTYPE html>
<html lang="{{ $activeLanguageCode }}" dir="{{ $activeDirection }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('themes._shared.seo')
    <link rel="stylesheet" href="{{ asset('themes/' . $theme_slug . '/css/style.css') }}">
    @stack('styles')
    <style>
        :root {
            --primary-color: {{ $palette['primary_color'] }};
            --secondary-color: {{ $palette['secondary_color'] }};
            --accent-color: {{ $palette['accent_color'] }};
            --bg-color: {{ $palette['bg_color'] }};
            --text-color: {{ $palette['text_color'] }};
            --border-color: {{ $palette['border_color'] }};
            --container-width: {{ $palette['container_width'] }};
            --font-family: {!! $palette['font_family'] !!};
            --body-font-size: {{ $palette['body_font_size'] }};
            --line-height: {{ $palette['line_height'] }};
            --block-gap: {{ $palette['block_gap'] }};
            --section-padding: {{ $palette['section_padding'] }};
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            font-family: var(--font-family);
            font-size: var(--body-font-size);
            line-height: var(--line-height);
            background: var(--bg-color);
            color: var(--text-color);
            -webkit-font-smoothing: antialiased;
        }
        .container { max-width: var(--container-width); margin: 0 auto; padding: 0 1.5rem; }
        a { color: var(--primary-color); text-decoration: none; }
        h1, h2, h3, h4 { font-weight: 700; line-height: 1.2; color: var(--primary-color); }
        .site-nav { position: sticky; top: 0; z-index: 100; background: #fff; border-bottom: 1px solid var(--border-color); transition: box-shadow 0.2s; }
        .site-nav.is-scrolled { box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
        .site-nav .nav-container { display: flex; align-items: center; justify-content: space-between; padding: 0.9rem 0; }
        .site-nav .nav-logo { display: flex; align-items: center; gap: 0.5rem; font-size: 1.35rem; font-weight: 800; color: var(--primary-color); letter-spacing: -0.02em; }
        .site-nav .nav-logo img { height: 34px; width: auto; }
        .site-nav nav { display: flex; align-items: center; gap: 1.5rem; }
        .site-nav .nav-menu { display: flex; list-style: none; gap: 1.35rem; margin: 0; padding: 0; }
        .site-nav .nav-menu a { color: var(--text-color); font-weight: 500; font-size: 0.95rem; padding: 0.25rem 0; }
        .site-nav .nav-menu a:hover { color: var(--accent-color); }
        .site-nav .nav-cta { background: var(--accent-color); color: #fff; padding: 0.5rem 1.1rem; border-radius: 0.375rem; font-weight: 600; }
        .site-nav .nav-cta:hover { background: #b45309; color: #fff; }
        .nav-toggle { display: none; background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--primary-color); }
        main { min-height: 70vh; }
        .site-footer { background: #172554; color: #cbd5e1; padding: 3rem 0 1.5rem; margin-top: 3rem; }
        .site-footer .footer-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; padding-bottom: 2rem; }
        .site-footer h3 { color: #fff; font-size: 1.05rem; margin-bottom: 1rem; }
        .site-footer a { color: #cbd5e1; font-size: 0.95rem; }
        .site-footer a:hover { color: #fff; }
        .site-footer ul { list-style: none; padding: 0; }
        .site-footer li { margin-bottom: 0.5rem; }
        .site-footer .footer-bottom { border-top: 1px solid rgba(255,255,255,0.15); padding-top: 1.5rem; text-align: center; font-size: 0.875rem; }
        .btn { display: inline-block; padding: 0.75rem 1.6rem; border-radius: 0.375rem; font-weight: 600; border: none; cursor: pointer; transition: all 0.2s; text-decoration: none; }
        .btn-primary { background: var(--primary-color); color: #fff; }
        .btn-primary:hover { background: #172554; color: #fff; }
        .btn-accent { background: var(--accent-color); color: #fff; }
        .btn-accent:hover { background: #b45309; color: #fff; }
        .btn-outline { background: transparent; color: var(--primary-color); border: 2px solid var(--primary-color); }
        .btn-outline:hover { background: var(--primary-color); color: #fff; }
        .card { background: #fff; border-radius: 0.75rem; border: 1px solid var(--border-color); box-shadow: 0 1px 3px rgba(0,0,0,0.06); overflow: hidden; transition: box-shadow 0.2s, transform 0.2s; }
        .card:hover { box-shadow: 0 12px 30px rgba(0,0,0,0.1); transform: translateY(-3px); }
        .card img { width: 100%; height: 180px; object-fit: cover; }
        .card .card-body { padding: 1.5rem; }
        .card .card-body h3 { font-size: 1.15rem; margin-bottom: 0.5rem; }
        .card .card-body p { color: var(--secondary-color); font-size: 0.95rem; }
        .post-meta { font-size: 0.85rem; color: var(--secondary-color); margin-bottom: 0.75rem; display: flex; gap: 0.75rem; flex-wrap: wrap; }
        .posts-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.75rem; }
        .section { padding: var(--section-padding) 0; }
        .section-title { text-align: center; margin-bottom: 2.5rem; }
        .section-title h2 { font-size: 2rem; margin-bottom: 0.5rem; }
        .section-title p { color: var(--secondary-color); }
        .post-content { line-height: 1.8; }
        .post-content h2, .post-content h3 { margin: 1.5rem 0 0.75rem; }
        .post-content p { margin-bottom: 1rem; }
        .post-content img { max-width: 100%; border-radius: 0.5rem; margin: 1rem 0; }
        .alert { padding: 1rem 1.25rem; border-radius: 0.5rem; margin-bottom: 1rem; }
        .alert-success { background: #d1fae5; border: 1px solid #10b981; color: #065f46; }
        .alert-danger { background: #fee2e2; border: 1px solid #ef4444; color: #991b1b; }
        @media (max-width: 860px) {
            .site-nav .nav-menu, .site-nav .nav-cta { display: none; }
            .site-nav .nav-toggle { display: block; }
            .site-nav.nav-open .nav-menu { display: flex; flex-direction: column; gap: 0.75rem; padding: 1rem 0; }
            .site-nav.nav-open .nav-cta { display: inline-block; padding: 0.5rem 1.25rem; }
            .site-nav nav { flex-direction: column; align-items: flex-end; }
            .site-nav.nav-open nav { flex-direction: column; align-items: flex-start; }
        }
    </style>
    @include('themes._shared.pattern-styles')
    @if(!empty($settings->custom_css))
        <style id="site-custom-css">{!! $settings->custom_css !!}</style>
    @endif
    @if(!empty($settings->analytics_code))
        {!! $settings->analytics_code !!}
    @endif
    @if(function_exists('wp_head'))
        @php wp_head(); @endphp
    @endif
</head>
<body>
    @if($theme_preview ?? false)
        <div style="background:#111827;color:#f9fafb;padding:0.6rem 1rem;text-align:center;font-size:0.875rem;">
            Previewing the <strong>{{ $theme_slug }}</strong> theme. Visitors still see the active theme.
            <a href="{{ url('/') }}" style="color:#93c5fd;margin-left:0.75rem;text-decoration:underline;">Exit preview</a>
        </div>
    @endif
    @if($preview ?? false)
        @php
            $previewPost = $post ?? $page ?? null;
            $previewKind = ($previewPost?->post_type ?? 'page') === 'post' ? 'post' : 'page';
            $previewEditUrl = $previewPost
                ? url('/admin/' . ($previewKind === 'post' ? 'posts' : 'pages') . '/' . $previewPost->id . '/edit')
                : null;
            $previewPublicUrl = $previewPost
                ? url(($previewKind === 'post' ? '/posts/' : '/pages/') . $previewPost->slug)
                : url('/');
        @endphp
        <div style="background:#b45309;color:#fff;padding:0.6rem 1rem;text-align:center;font-size:0.875rem;">
            <strong>Preview</strong> — this {{ $previewKind }} is
            <strong>{{ ucfirst($previewPost->status ?? 'draft') }}</strong>
            and is not visible to visitors.
            @if($previewEditUrl)
                <a href="{{ $previewEditUrl }}" style="color:#fde68a;margin-left:0.75rem;text-decoration:underline;">Edit</a>
            @endif
            <a href="{{ $previewPublicUrl }}" style="color:#fde68a;margin-left:0.75rem;text-decoration:underline;">Exit preview</a>
        </div>
    @endif
    <nav class="site-nav" aria-label="Main navigation">
        <div class="container nav-container">
            <a href="{{ url('/') }}" class="nav-logo">
                @if(!empty($settings->logo_url))
                    <img src="{{ $settings->logo_url }}" alt="{{ $settings->resolved_site_title ?? $site->name }}">
                @endif
                <span>{{ $settings->resolved_site_title ?? ($site->name ?? 'Business') }}</span>
            </a>
            <button class="nav-toggle" type="button" aria-label="Toggle menu" aria-expanded="false" onclick="document.querySelector('.site-nav').classList.toggle('nav-open')">&#9776;</button>
            <nav>
                @include('themes._shared.nav', ['menu' => $headerMenu ?? null, 'navClass' => 'nav-menu'])
                <a href="{{ url('/contact') }}" class="nav-cta">Get a Quote</a>
            </nav>
        </div>
    </nav>
    <main>
        @yield('content')
    </main>
    @include('themes._shared.widget-area', ['location' => 'before-footer'])
    <footer class="site-footer">
        <div class="container">
            <div class="footer-container">
                <div>
                    <h3>{{ $settings->resolved_site_title ?? ($site->name ?? 'Business') }}</h3>
                    <p>{{ $settings->resolved_site_description ?? ($site->description ?? '') }}</p>
                </div>
                <div>
                    <h3>Quick Links</h3>
                    <ul>
                        @if(($footerMenu ?? null) && $footerMenu->rootItems->isNotEmpty())
                            @foreach($footerMenu->rootItems as $item)
                                <li><a href="{{ $item->resolved_url }}">{{ $item->resolved_title }}</a></li>
                            @endforeach
                        @else
                            <li><a href="{{ url('/') }}">Home</a></li>
                            <li><a href="{{ url('/about') }}">About</a></li>
                            <li><a href="{{ url('/contact') }}">Contact</a></li>
                        @endif
                    </ul>
                </div>
                <div>
                    <h3>Get in Touch</h3>
                    <ul>
                        @if($settings->admin_email ?? null)
                            <li><a href="mailto:{{ $settings->admin_email }}">{{ $settings->admin_email }}</a></li>
                        @endif
                        <li><a href="{{ url('/search') }}">Search</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} {{ $settings->resolved_site_title ?? ($site->name ?? 'Business') }}. All rights reserved.</p>
            </div>
        </div>
    </footer>
    @include('themes._shared.chat-widget')

    @if(!empty($settings->custom_js))
        <script id="site-custom-js">{!! $settings->custom_js !!}</script>
    @endif
    @if(function_exists('wp_footer'))
        @php wp_footer(); @endphp
    @endif
    @stack('scripts')
    <script src="{{ asset('themes/' . $theme_slug . '/js/main.js') }}"></script>
</body>
</html>
