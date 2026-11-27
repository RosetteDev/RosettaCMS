@php
    $theme_slug = $theme_slug ?? ($site->theme ?? 'photography');
    $theme_options = $theme_options ?? [];
    $palette = array_merge([
        'primary_color' => '#f8fafc',
        'secondary_color' => '#94a3b8',
        'accent_color' => '#f59e0b',
        'bg_color' => '#0f172a',
        'text_color' => '#f1f5f9',
        'border_color' => '#1e293b',
        'font_family' => "Georgia, 'Times New Roman', serif",
        'container_width' => '1280px',
        'body_font_size' => '17px',
        'line_height' => '1.7',
        'block_gap' => '2.5rem',
        'section_padding' => '5rem',
    ], array_filter($theme_options, fn ($value) => $value !== null && $value !== ''));
@endphp
@php
    $activeLanguage = $activeLanguage ?? (request()->attributes->get('activeLanguage') ?? \App\Models\Language::defaultLanguage());
    $activeLanguageCode = $activeLanguage?->code ?? app()->getLocale();
    $activeDirection = $activeLanguage?->isRtl() ? 'rtl' : 'ltr';
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
            --section-padding: {{ $palette['section_padding'] }};
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body { font-family: var(--font-family); font-size: var(--body-font-size); line-height: var(--line-height); background: var(--bg-color); color: var(--text-color); -webkit-font-smoothing: antialiased; }
        .container { max-width: var(--container-width); margin: 0 auto; padding: 0 1.5rem; }
        a { color: var(--accent-color); text-decoration: none; }
        .site-nav { position: sticky; top: 0; z-index: 100; background: rgba(15,23,42,0.85); backdrop-filter: blur(12px); border-bottom: 1px solid var(--border-color); }
        .site-nav .nav-container { display: flex; align-items: center; justify-content: space-between; padding: 1rem 0; }
        .site-nav .nav-logo { font-size: 1.4rem; font-weight: 700; color: #f8fafc; letter-spacing: 0.04em; font-family: Georgia, serif; }
        .site-nav nav { display: flex; align-items: center; gap: 1.75rem; }
        .site-nav .nav-menu { display: flex; list-style: none; gap: 1.5rem; margin: 0; padding: 0; }
        .site-nav .nav-menu a { color: #cbd5e1; font-family: -apple-system, sans-serif; font-size: 0.85rem; letter-spacing: 0.06em; text-transform: uppercase; }
        .site-nav .nav-menu a:hover { color: var(--accent-color); }
        .nav-toggle { display: none; background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #f8fafc; }
        main { min-height: 70vh; }
        .site-footer { background: #020617; border-top: 1px solid var(--border-color); padding: 3.5rem 0 1.5rem; margin-top: 3rem; }
        .site-footer .footer-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 2rem; padding-bottom: 2rem; }
        .site-footer h3 { color: #f8fafc; font-size: 0.85rem; letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 1rem; font-family: -apple-system, sans-serif; }
        .site-footer a { color: #94a3b8; font-family: -apple-system, sans-serif; font-size: 0.9rem; }
        .site-footer a:hover { color: var(--accent-color); }
        .site-footer ul { list-style: none; padding: 0; }
        .site-footer li { margin-bottom: 0.5rem; }
        .site-footer .footer-bottom { border-top: 1px solid var(--border-color); padding-top: 1.5rem; text-align: center; font-size: 0.8rem; color: #64748b; font-family: -apple-system, sans-serif; }
        .btn { display: inline-block; padding: 0.75rem 1.75rem; border-radius: 999px; font-family: -apple-system, sans-serif; font-weight: 600; font-size: 0.9rem; border: none; cursor: pointer; transition: all 0.2s; text-decoration: none; }
        .btn-primary { background: var(--accent-color); color: #0f172a; }
        .btn-primary:hover { background: #fbbf24; }
        .btn-outline { background: transparent; color: #f8fafc; border: 1px solid rgba(248,250,252,0.4); }
        .btn-outline:hover { border-color: #f8fafc; background: rgba(248,250,252,0.08); }
        .post-meta { font-family: -apple-system, sans-serif; font-size: 0.8rem; letter-spacing: 0.06em; text-transform: uppercase; color: #94a3b8; margin-bottom: 0.75rem; display: flex; gap: 0.75rem; flex-wrap: wrap; }
        .section { padding: var(--section-padding) 0; }
        .section-title { text-align: center; margin-bottom: 2.5rem; }
        .section-title h2 { font-size: 2.25rem; margin-bottom: 0.5rem; }
        .section-title p { color: #94a3b8; font-family: -apple-system, sans-serif; }
        .post-content { line-height: 1.8; font-size: 1.1rem; }
        .post-content h2, .post-content h3 { margin: 1.5rem 0 0.75rem; }
        .post-content p { margin-bottom: 1rem; }
        .alert { padding: 1rem 1.25rem; border-radius: 0.5rem; margin-bottom: 1rem; }
        .alert-success { background: #064e3b; border: 1px solid #10b981; color: #a7f3d0; }
        .alert-danger { background: #450a0a; border: 1px solid #ef4444; color: #fca5a5; }
        @media (max-width: 860px) {
            .site-nav .nav-menu { display: none; }
            .site-nav .nav-toggle { display: block; }
            .site-nav.nav-open .nav-menu { display: flex; flex-direction: column; gap: 0.75rem; padding: 1rem 0; }
            .site-nav nav { flex-direction: column; align-items: flex-end; }
            .site-nav.nav-open nav { flex-direction: column; align-items: flex-start; }
        }
    </style>
    @include('themes._shared.pattern-styles')
    <style>
        .lightbox-overlay { position: fixed; inset: 0; background: rgba(5,8,15,0.92); z-index: 1000; display: none; align-items: center; justify-content: center; padding: 2rem; cursor: zoom-out; }
        .lightbox-overlay.open { display: flex; }
        .lightbox-overlay img { max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 0.5rem; box-shadow: 0 20px 60px rgba(0,0,0,0.6); }
        .lightbox-close { position: absolute; top: 1.25rem; right: 1.5rem; background: none; border: none; color: #f8fafc; font-size: 2.5rem; cursor: pointer; line-height: 1; }
        .portfolio-item img { transition: transform 0.3s ease; }
        .portfolio-item:hover img { transform: scale(1.04); }
    </style>
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
    <nav class="site-nav" aria-label="Main navigation">
        <div class="container nav-container">
            <a href="{{ url('/') }}" class="nav-logo">{{ $settings->resolved_site_title ?? ($site->name ?? 'Portfolio') }}</a>
            <button class="nav-toggle" type="button" aria-label="Toggle menu" aria-expanded="false" onclick="document.querySelector('.site-nav').classList.toggle('nav-open')">&#9776;</button>
            <nav>
                @include('themes._shared.nav', ['menu' => $headerMenu ?? null, 'navClass' => 'nav-menu'])
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
                    <h3>{{ $settings->resolved_site_title ?? ($site->name ?? 'Portfolio') }}</h3>
                    <p style="color:#94a3b8;font-family:-apple-system,sans-serif;font-size:0.9rem;">{{ $settings->resolved_site_description ?? ($site->description ?? '') }}</p>
                </div>
                <div>
                    <h3>Explore</h3>
                    <ul>
                        <li><a href="{{ url('/') }}">Home</a></li>
                        <li><a href="{{ url('/about') }}">About</a></li>
                        <li><a href="{{ url('/contact') }}">Contact</a></li>
                        <li><a href="{{ url('/search') }}">Search</a></li>
                    </ul>
                </div>
                <div>
                    <h3>Contact</h3>
                    <ul>
                        @if($settings->admin_email ?? null)
                            <li><a href="mailto:{{ $settings->admin_email }}">{{ $settings->admin_email }}</a></li>
                        @endif
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} {{ $settings->resolved_site_title ?? ($site->name ?? 'Portfolio') }}. All rights reserved.</p>
            </div>
        </div>
    </footer>
    @if(!empty($settings->custom_js))
        <script id="site-custom-js">{!! $settings->custom_js !!}</script>
    @endif
    @if(function_exists('wp_footer'))
        @php wp_footer(); @endphp
    @endif
    @stack('scripts')
    <div class="lightbox-overlay" id="lightbox" role="dialog" aria-modal="true" aria-label="Image preview">
        <button class="lightbox-close" aria-label="Close preview">&times;</button>
        <img src="" alt="">
    </div>
    <script src="{{ asset('themes/' . $theme_slug . '/js/main.js') }}"></script>
</body>
</html>
