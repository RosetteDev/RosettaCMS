@php
    $theme_slug = $theme_slug ?? ($site->theme ?? 'rosetta');
    $theme_options = $theme_options ?? [];
    $palette = array_merge([
        'primary_color' => '#3b82f6',
        'secondary_color' => '#6b7280',
        'accent_color' => '#f59e0b',
        'bg_color' => '#ffffff',
        'text_color' => '#1f2937',
        'border_color' => '#e5e7eb',
        'font_family' => "'Segoe UI', system-ui, -apple-system, sans-serif",
        'container_width' => '1200px',
        'body_font_size' => '16px',
        'line_height' => '1.6',
        'block_gap' => '2rem',
        'section_padding' => '3rem',
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
    
    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('themes/' . $theme_slug . '/css/style.css') }}">
    @stack('styles')
    
    <style>
        :root {
            --primary-color: {{ $palette['primary_color'] }};
            --secondary-color: {{ $palette['secondary_color'] }};
            --accent-color: {{ $palette['accent_color'] }};
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --info-color: #06b6d4;
            --dark-color: #1f2937;
            --light-color: #f3f4f6;
            --bg-color: {{ $palette['bg_color'] }};
            --text-color: {{ $palette['text_color'] }};
            --border-color: {{ $palette['border_color'] }};
            --container-width: {{ $palette['container_width'] }};
            --font-family: {!! $palette['font_family'] !!};
            --body-font-size: {{ $palette['body_font_size'] ?? '16px' }};
            --line-height: {{ $palette['line_height'] ?? '1.6' }};
            --block-gap: {{ $palette['block_gap'] ?? '2rem' }};
            --section-padding: {{ $palette['section_padding'] ?? '3rem' }};
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: var(--font-family);
            font-size: var(--body-font-size);
            background-color: var(--bg-color);
            color: var(--text-color);
            line-height: var(--line-height);
        }
        
        /* Container */
        .container {
            max-width: var(--container-width);
            margin: 0 auto;
            padding: 0 1.5rem;
        }
        
        /* Navigation */
        .nav {
            background-color: white;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
        }
        
        .nav-logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
            letter-spacing: -0.025em;
        }
        
        .nav-menu {
            display: flex;
            list-style: none;
            gap: 2rem;
        }
        
        .nav-menu a {
            text-decoration: none;
            color: var(--text-color);
            font-weight: 500;
            font-size: 0.95rem;
            transition: color 0.2s;
        }
        
        .nav-menu a:hover {
            color: var(--primary-color);
        }
        
        /* Hero */
        .hero {
            padding: 5rem 0;
            background: white;
            text-align: center;
        }
        
        .hero h1 {
            font-size: 3rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }
        
        .hero p {
            font-size: 1.25rem;
            color: var(--secondary-color);
            max-width: 600px;
            margin: 0 auto;
        }
        
        /* Main Content */
        main {
            min-height: 80vh;
        }
        
        /* Content */
        .content {
            padding: 4rem 0;
        }
        
        /* Footer */
        .footer {
            background-color: var(--primary-color);
            color: white;
            padding: 3rem 0;
            margin-top: 4rem;
        }
        
        .footer-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
        }
        
        .footer-section h3 {
            margin-bottom: 1rem;
            color: white;
            font-size: 1.1rem;
        }
        
        .footer-section ul {
            list-style: none;
        }
        
        .footer-section li {
            margin-bottom: 0.5rem;
        }
        
        .footer-section a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            font-size: 0.95rem;
            transition: opacity 0.2s;
        }
        
        .footer-section a:hover {
            opacity: 1;
        }
        
        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            margin-top: 2rem;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        
        /* Cards */
        .card {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
            padding: 1.5rem;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        /* Buttons */
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background-color: var(--primary-color);
            color: white;
            border-radius: 0.375rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }
        
        .btn:hover {
            background-color: var(--secondary-color);
            transform: translateY(-1px);
        }
        
        /* Forms */
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.375rem;
            font-family: var(--font-family);
            font-size: 1rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(var(--primary-color), 0.1);
        }
        
        /* Posts List */
        .posts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }
        
        .post-item {
            margin-bottom: 2rem;
        }
        
        .post-item h2 {
            margin-bottom: 0.75rem;
        }
        
        .post-item h2 a {
            color: var(--text-color);
            text-decoration: none;
            transition: color 0.2s;
        }
        
        .post-item h2 a:hover {
            color: var(--primary-color);
        }
        
        .post-meta {
            font-size: 0.875rem;
            color: var(--secondary-color);
            margin-bottom: 1rem;
        }
        
        /* Post Content */
        .post-content {
            line-height: 1.7;
            font-size: 1.125rem;
        }
        
        .post-content img {
            max-width: 100%;
            border-radius: 0.5rem;
            margin: 1.5rem 0;
        }
        
        /* Tags */
        .tag {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background-color: #f3f4f6;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            text-decoration: none;
            color: var(--text-color);
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }
        
        .tag:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        /* YouTube Embed */
        .youtube-embed {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
            overflow: hidden;
            margin: 1rem 0;
        }
        
        .youtube-embed iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
        
        @media (max-width: 768px) {
            .nav-menu {
                display: none;
            }
            
            .hero h1 {
                font-size: 2rem;
            }
            
            .posts-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    {{-- Fallback styles for elements shared by the built-in themes. --}}
    <style>
        .main-content { min-height: calc(100vh - 260px); }
        .btn-outline {
            border: 2px solid var(--primary-color);
            background: transparent;
            color: var(--primary-color);
        }
        .btn-outline:hover {
            background: var(--primary-color);
            color: #fff;
        }
        .block { margin-bottom: var(--block-gap); }
        .section, .section-padding { padding-top: var(--section-padding); padding-bottom: var(--section-padding); }
        .section-title { text-align: center; margin-bottom: 2rem; }
        .section-title h2 { font-size: 2rem; margin-bottom: .5rem; }
        .section-title p { color: var(--secondary-color); }
        .feature-icon { font-size: 2.5rem; margin-bottom: 1rem; color: var(--primary-color); }
        .contact-form { max-width: 640px; margin: 0 auto; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: .5rem; font-weight: 500; }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: .75rem;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 1rem;
        }
        .form-group textarea { height: 150px; resize: vertical; }
        .alert { padding: 1rem; border-radius: 6px; margin-bottom: 1rem; }
        .alert-success { background: #d1fae5; border: 1px solid var(--success-color); color: #065f46; }
        .alert-danger { background: #fee2e2; border: 1px solid var(--danger-color); color: #991b1b; }
        .alert-warning { background: #fef3c7; border: 1px solid var(--warning-color); color: #92400e; }
        .alert-info { background: #cffafe; border: 1px solid var(--info-color); color: #0e7490; }
        .post-content img { max-width: 100%; height: auto; border-radius: .5rem; }
        .post-content h2, .post-content h3 { margin-top: 1.5rem; }
        .post-content p { margin-bottom: 1rem; }
        .post-content ul, .post-content ol { margin: 0 0 1rem 1.5rem; }
        .pagination { display: flex; gap: .5rem; justify-content: center; margin: 2rem 0; }
        .pagination a, .pagination span { padding: .5rem .85rem; border: 1px solid var(--border-color); border-radius: 6px; text-decoration: none; }
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
    <!-- Navigation -->
    <nav class="nav">
        <div class="container nav-container">
            <a href="{{ url('/') }}" class="nav-logo">
                @if(!empty($settings->logo_url))
                    <img src="{{ $settings->logo_url }}" alt="{{ $settings->site_title ?? $site->name }}" style="height: 32px;">
                @else
                    {{ $settings->site_title ?? ($site->name ?? 'Rosetta CMS') }}
                @endif
            </a>
            
            @include('themes._shared.nav', ['menu' => $headerMenu ?? null, 'navClass' => 'nav-menu'])
            
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                @if($availableLanguages->count() > 1)
                    <div class="lang-switcher" style="display: flex; gap: 0.25rem; align-items: center;">
                        @foreach($availableLanguages as $lang)
                            <a href="{{ route('language.switch', $lang->code) }}"
                               style="padding: 0.35rem 0.6rem; font-size: 0.8rem; border-radius: 6px; text-decoration: none;
                                      {{ $lang->code === $activeLanguageCode ? 'background: var(--accent-color); color: #fff;' : 'background: transparent; color: var(--text-color); border: 1px solid var(--border-color);' }}"
                               title="{{ $lang->name }}">{{ $lang->flag ?? strtoupper($lang->code) }}</a>
                        @endforeach
                    </div>
                @endif
                @auth
                    @if(auth()->user()->isStaff())
                        <a href="{{ route('dashboard') }}" class="btn" style="padding: 0.5rem 1rem; font-size: 0.9rem;">Admin</a>
                    @else
                        <a href="{{ route('profile') }}" class="btn" style="padding: 0.5rem 1rem; font-size: 0.9rem;">Account</a>
                    @endif
                @endauth
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="main-content">
        @yield('content')
    </main>
    
    <!-- Footer -->
    <footer class="footer">
        @include('themes._shared.footer')
    </footer>
    
    @if(!empty($settings->custom_js))
        <script id="site-custom-js">{!! $settings->custom_js !!}</script>
    @endif
    @if(function_exists('wp_footer'))
        @php wp_footer(); @endphp
    @endif
    @stack('scripts')
</body>
</html>