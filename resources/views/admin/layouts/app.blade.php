<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Admin Dashboard' }} - Rosetta CMS</title>
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
        
        .sidebar {
            display: flex;
            flex-direction: column;
            width: 250px;
            float: left;
            margin-right: 2rem;
        }
        
        .sidebar a {
            display: block;
            padding: 0.75rem 1rem;
            color: var(--text-color);
            text-decoration: none;
            border-radius: 6px;
            margin-bottom: 0.5rem;
            transition: all 0.2s;
        }
        
        .sidebar a:hover {
            background-color: var(--light-color);
            color: var(--primary-color);
        }
        
        .sidebar a.active {
            background-color: var(--primary-color);
            color: white;
        }
        
        .main-content {
            margin-left: 270px;
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
        
        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #dc2626;
        }
        
        .btn-outline {
            background-color: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
        }
        
        .btn-outline:hover {
            background-color: var(--primary-color);
            color: white;
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
                    <li><a href="{{ url('/') }}">Public Site</a></li>
                    <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ url('/admin/logout') }}">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="sidebar">
            <a href="{{ url('/admin') }}">Dashboard</a>
            <a href="{{ url('/admin/posts') }}">Posts</a>
            <a href="{{ url('/admin/media') }}">Media</a>
            <a href="{{ url('/admin/sites') }}">Sites</a>
            <a href="{{ url('/admin/settings') }}">Settings</a>
        </div>
        
        <div class="main-content">
            @yield('content')
        </div>
    </div>

    @stack('scripts')
</body>
</html>
