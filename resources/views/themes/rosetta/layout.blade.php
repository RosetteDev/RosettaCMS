<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Rosetta CMS' }}</title>
    <meta name="description" content="{{ $description ?? '' }}">
    
    <!-- SEO Meta Tags -->
    @if(isset($seo))
        {{ $seo }}
    @endif
    
    <!-- Styles -->
    @stack('styles')
    
    <style>
        :root {
            --primary-color: #3b82f6;
            --secondary-color: #6b7280;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --info-color: #06b6d4;
            --dark-color: #1f2937;
            --light-color: #f3f4f6;
            
            --bg-color: #ffffff;
            --text-color: #1f2937;
            --border-color: #e5e7eb;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        header {
            background-color: var(--dark-color);
            color: white;
            padding: 1rem 0;
            margin-bottom: 2rem;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        .logo a {
            color: white;
            text-decoration: none;
        }
        
        nav ul {
            display: flex;
            list-style: none;
            gap: 1rem;
        }
        
        nav a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: background-color 0.2s;
        }
        
        nav a:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        main {
            min-height: calc(100vh - 200px);
        }
        
        footer {
            background-color: var(--dark-color);
            color: white;
            padding: 2rem 0;
            margin-top: 3rem;
        }
        
        .footer-content {
            text-align: center;
        }
        
        .hero {
            text-align: center;
            padding: 4rem 0;
            background: linear-gradient(135deg, var(--primary-color), #2563eb);
            color: white;
            margin-bottom: 3rem;
        }
        
        .hero h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #2563eb;
        }
        
        .btn-outline {
            background-color: transparent;
            border: 2px solid white;
            color: white;
        }
        
        .btn-outline:hover {
            background-color: white;
            color: var(--primary-color);
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .section-title h2 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        .card {
            background-color: var(--light-color);
            border-radius: 8px;
            padding: 1.5rem;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .card h3 {
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }
        
        .card p {
            color: var(--text-color);
        }
        
        .feature-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }
        
        .contact-form {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 1rem;
        }
        
        .form-group textarea {
            height: 150px;
            resize: vertical;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .alert {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
        }
        
        .alert-success {
            background-color: #d1fae5;
            border: 1px solid var(--success-color);
            color: #065f46;
        }
        
        .alert-danger {
            background-color: #fee2e2;
            border: 1px solid var(--danger-color);
            color: #991b1b;
        }
        
        .alert-warning {
            background-color: #fef3c7;
            border: 1px solid var(--warning-color);
            color: #92400e;
        }
        
        .alert-info {
            background-color: #cffafe;
            border: 1px solid var(--info-color);
            color: #0e7490;
        }
    </style>
    
    @yield('styles')
</head>
<body>
    <header>
        <div class="container header-content">
            <div class="logo">
                <a href="{{ url('/') }}">Rosetta CMS</a>
            </div>
            <nav>
                <ul>
                    <li><a href="{{ url('/') }}">Home</a></li>
                    <li><a href="{{ url('/about') }}">About</a></li>
                    <li><a href="{{ url('/contact') }}">Contact</a></li>
                    <li><a href="{{ url('/admin') }}">Admin</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        @yield('content')
    </main>

    <footer>
        <div class="container footer-content">
            <p>&copy; {{ date('Y') }} Rosetta CMS. All rights reserved.</p>
            <p style="margin-top: 0.5rem; font-size: 0.9rem; color: #9ca3af;">
                Open Source Content Management System
            </p>
        </div>
    </footer>

    @yield('scripts')
</body>
</html>
