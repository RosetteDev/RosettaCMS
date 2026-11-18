<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('themes._shared.seo')
    
    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('themes/elegant/css/style.css') }}">
    @stack('styles')
    
    <style>
        :root {
            --primary-color: {{ $theme_options['primary_color'] ?? '#1e3a8a' }};
            --secondary-color: {{ $theme_options['secondary_color'] ?? '#64748b' }};
            --accent-color: {{ $theme_options['accent_color'] ?? '#d97706' }};
            --bg-color: {{ $theme_options['bg_color'] ?? '#fafafa' }};
            --text-color: {{ $theme_options['text_color'] ?? '#1f2937' }};
            --container-width: {{ $theme_options['container_width'] ?? '1100px' }};
            --font-family: {{ $theme_options['font_family'] ?? 'Georgia, "Times New Roman", serif' }};
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: var(--font-family);
            background-color: var(--bg-color);
            color: var(--text-color);
            line-height: 1.8;
        }
        
        /* Container */
        .container {
            max-width: var(--container-width);
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        /* Navigation */
        .nav {
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 0;
        }
        
        .nav-logo {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
            font-family: var(--font-family);
        }
        
        .nav-menu {
            display: flex;
            list-style: none;
            gap: 2.5rem;
        }
        
        .nav-menu a {
            text-decoration: none;
            color: var(--text-color);
            font-size: 1rem;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .nav-menu a:hover {
            color: var(--primary-color);
        }
        
        /* Hero */
        .hero {
            padding: 6rem 0;
            background: white;
            text-align: center;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .hero h1 {
            font-size: 3.5rem;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 1.5rem;
            color: var(--primary-color);
            font-family: var(--font-family);
        }
        
        .hero p {
            font-size: 1.375rem;
            color: var(--secondary-color);
            max-width: 700px;
            margin: 0 auto;
            font-family: var(--font-family);
        }
        
        /* Main Content */
        main {
            min-height: 80vh;
        }
        
        /* Content */
        .content {
            padding: 5rem 0;
        }
        
        /* Footer */
        .footer {
            background-color: var(--primary-color);
            color: white;
            padding: 4rem 0;
            margin-top: 5rem;
        }
        
        .footer-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
        }
        
        .footer-section h3 {
            margin-bottom: 1.5rem;
            color: white;
            font-size: 1.25rem;
            font-family: var(--font-family);
        }
        
        .footer-section ul {
            list-style: none;
        }
        
        .footer-section li {
            margin-bottom: 0.75rem;
        }
        
        .footer-section a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: opacity 0.3s;
        }
        
        .footer-section a:hover {
            opacity: 1;
        }
        
        .footer-bottom {
            text-align: center;
            padding-top: 3rem;
            margin-top: 3rem;
            border-top: 1px solid rgba(255,255,255,0.15);
        }
        
        /* Cards */
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            padding: 2rem;
            transition: transform 0.3s, box-shadow 0.3s;
            margin-bottom: 2rem;
        }
        
        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.1);
        }
        
        /* Buttons */
        .btn {
            display: inline-block;
            padding: 0.875rem 1.75rem;
            background-color: var(--primary-color);
            color: white;
            border-radius: 4px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-family: var(--font-family);
        }
        
        .btn:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        /* Forms */
        .form-control {
            width: 100%;
            padding: 0.875rem 1.25rem;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-family: var(--font-family);
            font-size: 1rem;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(var(--primary-color), 0.1);
        }
        
        /* Posts List */
        .posts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2.5rem;
        }
        
        .post-item {
            margin-bottom: 2rem;
        }
        
        .post-item h2 {
            margin-bottom: 1rem;
            font-family: var(--font-family);
        }
        
        .post-item h2 a {
            color: var(--text-color);
            text-decoration: none;
            transition: color 0.3s;
            font-size: 1.5rem;
        }
        
        .post-item h2 a:hover {
            color: var(--primary-color);
        }
        
        .post-meta {
            font-size: 0.95rem;
            color: var(--secondary-color);
            margin-bottom: 1rem;
            font-style: italic;
        }
        
        /* Post Content */
        .post-content {
            line-height: 1.9;
            font-size: 1.25rem;
        }
        
        .post-content h2 {
            margin: 2.5rem 0 1rem;
            font-family: var(--font-family);
            font-size: 2rem;
        }
        
        .post-content p {
            margin-bottom: 1.5rem;
        }
        
        .post-content img {
            max-width: 100%;
            border-radius: 8px;
            margin: 2rem 0;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        /* Tags */
        .tag {
            display: inline-block;
            padding: 0.375rem 1rem;
            background-color: #f3f4f6;
            border-radius: 4px;
            font-size: 0.95rem;
            text-decoration: none;
            color: var(--text-color);
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
            transition: all 0.3s;
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
            margin: 2rem 0;
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
                font-size: 2.25rem;
            }
            
            .posts-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="nav">
        <div class="container nav-container">
            <a href="{{ url('/') }}" class="nav-logo">
                @if(!empty($settings->logo_url))
                    <img src="{{ $settings->logo_url }}" alt="{{ $settings->site_title ?? $site->name }}" style="height: 32px;">
                @else
                    {{ $settings->site_title ?? ($site->name ?? 'Elegant CMS') }}
                @endif
            </a>
            
            @include('themes._shared.nav', ['menu' => $headerMenu ?? null, 'navClass' => 'nav-menu'])
            
            <div>
                @auth
                    <a href="{{ route('dashboard') }}" class="btn" style="padding: 0.625rem 1.25rem; font-size: 0.9rem;">Admin</a>
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
    
    @stack('scripts')
</body>
</html>