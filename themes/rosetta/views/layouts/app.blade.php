<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('themes._shared.seo')
    
    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('themes/' . ($theme_slug ?? 'rosetta') . '/css/style.css') }}">
    @stack('styles')
    
    <style>
        :root {
            --primary-color: {{ $theme_options['primary_color'] ?? '#3b82f6' }};
            --secondary-color: {{ $theme_options['secondary_color'] ?? '#6b7280' }};
            --success-color: {{ $theme_options['success_color'] ?? '#10b981' }};
            --danger-color: {{ $theme_options['danger_color'] ?? '#ef4444' }};
            --warning-color: {{ $theme_options['warning_color'] ?? '#f59e0b' }};
            --info-color: {{ $theme_options['info_color'] ?? '#06b6d4' }};
            --dark-color: {{ $theme_options['dark_color'] ?? '#1f2937' }};
            --light-color: {{ $theme_options['light_color'] ?? '#f3f4f6' }};
            
            --bg-color: {{ $theme_options['bg_color'] ?? '#ffffff' }};
            --text-color: {{ $theme_options['text_color'] ?? '#1f2937' }};
            --border-color: {{ $theme_options['border_color'] ?? '#e5e7eb' }};
            --container-width: {{ $theme_options['container_width'] ?? '1200px' }};
            --font-family: {{ $theme_options['font_family'] ?? 'system-ui, sans-serif' }};
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html {
            scroll-behavior: smooth;
        }
        
        body {
            font-family: var(--font-family);
            background-color: var(--bg-color);
            color: var(--text-color);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }
        
        /* Container */
        .container {
            max-width: var(--container-width);
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        /* Navigation */
        .nav {
            background-color: var(--bg-color);
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
        }
        
        .nav-logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary-color);
            text-decoration: none;
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
            transition: color 0.3s;
        }
        
        .nav-menu a:hover {
            color: var(--primary-color);
        }
        
        /* Main Content */
        main {
            min-height: 80vh;
        }
        
        /* Footer */
        .footer {
            background-color: var(--dark-color);
            color: var(--light-color);
            padding: 3rem 0 1rem;
            margin-top: 4rem;
        }
        
        .footer-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .footer-section h3 {
            margin-bottom: 1rem;
            color: var(--primary-color);
        }
        
        .footer-section ul {
            list-style: none;
        }
        
        .footer-section li {
            margin-bottom: 0.5rem;
        }
        
        .footer-section a {
            color: var(--light-color);
            text-decoration: none;
            opacity: 0.8;
            transition: opacity 0.3s;
        }
        
        .footer-section a:hover {
            opacity: 1;
        }
        
        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            font-size: 0.9rem;
        }
        
        /* Buttons */
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border-radius: 0.375rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s;
            cursor: pointer;
            border: none;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-color);
            opacity: 0.9;
            transform: translateY(-1px);
        }
        
        /* Cards */
        .card {
            background: var(--bg-color);
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .card-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .card-content {
            padding: 1.5rem;
        }
        
        /* Forms */
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 0.375rem;
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
            transition: color 0.3s;
        }
        
        .post-item h2 a:hover {
            color: var(--primary-color);
        }
        
        .post-meta {
            font-size: 0.875rem;
            color: var(--secondary-color);
            margin-bottom: 1rem;
        }
        
        /* Tags */
        .tag {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background-color: var(--light-color);
            border-radius: 1rem;
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
        
        /* Hero Section */
        .hero {
            padding: 6rem 0;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        /* Alerts */
        .alert {
            padding: 1rem;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
        }
        
        .alert-success {
            background-color: var(--success-color);
            color: white;
        }
        
        .alert-danger {
            background-color: var(--danger-color);
            color: white;
        }
        
        .alert-warning {
            background-color: var(--warning-color);
            color: white;
        }
        
        .alert-info {
            background-color: var(--info-color);
            color: white;
        }
        
        /* Spacing utilities */
        .mt-1 { margin-top: 0.5rem; }
        .mt-2 { margin-top: 1rem; }
        .mt-3 { margin-top: 1.5rem; }
        .mt-4 { margin-top: 2rem; }
        .mb-1 { margin-bottom: 0.5rem; }
        .mb-2 { margin-bottom: 1rem; }
        .mb-3 { margin-bottom: 1.5rem; }
        .mb-4 { margin-bottom: 2rem; }
        
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
</head>
<body>
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
            
            <div>
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-primary">Admin</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary">Login</a>
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
    
    <!-- Scripts -->
    @stack('scripts')
    <script>
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            // Add any additional JavaScript here
        });
    </script>
</body>
</html>