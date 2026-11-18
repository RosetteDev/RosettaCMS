@php
    // Ensure the layout always has settings available, even for views whose
    // controller does not explicitly pass them.
    $settings = $settings ?? \App\Models\SiteSetting::forSite();
@endphp
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
    
    <!-- Favicon -->
    @if($settings->favicon_url ?? null)
        <link rel="icon" href="{{ $settings->favicon_url }}">
        <link rel="apple-touch-icon" href="{{ $settings->favicon_url }}">
    @endif
    
    <!-- Styles -->
    @stack('styles')
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: {{ $settings->primary_color ?? '#3b82f6' }};
            --secondary-color: {{ $settings->secondary_color ?? '#6b7280' }};
            --success-color: {{ $settings->accent_color ?? '#10b981' }};
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --info-color: #06b6d4;
            --dark-color: {{ $settings->text_color ?? '#1f2937' }};
            --light-color: {{ $settings->bg_color ?? '#ffffff' }};
            
            --bg-color: {{ $settings->bg_color ?? '#ffffff' }};
            --text-color: {{ $settings->text_color ?? '#1f2937' }};
            --border-color: {{ $settings->border_color ?? '#e5e7eb' }};
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
            overflow-x: hidden;
        }
        
        /* Admin Layout Container - avoid Bootstrap .container conflict */
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            gap: 2rem;
        }
        
        .admin-container > .sidebar {
            flex: 0 0 250px;
            order: 1;
        }
        
        .admin-container > .main-content {
            flex: 1;
            order: 2;
        }
        
        header {
            background-color: var(--dark-color);
            color: white;
            padding: 1rem 0;
            margin-bottom: 2rem;
            position: relative;
            z-index: 1000;
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
            position: relative;
            z-index: 1000;
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
        
        .sidebar-group {
            margin-bottom: 1rem;
        }
        
        .sidebar-group > a {
            font-weight: 600;
            border-top: 1px solid var(--border-color);
            padding-top: 1rem;
            margin-top: 0.5rem;
        }
        
        .sidebar-submenu {
            margin-left: 1rem;
            margin-top: 0.5rem;
        }
        
        .sidebar-submenu a {
            font-size: 0.9rem;
            padding: 0.5rem 0.75rem;
            margin-bottom: 0.25rem;
        }
        
        .main-content {
            margin-left: 0;
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
        
        /* Theme Preview Styles */
        .theme-preview-container {
            position: relative;
            height: 220px;
            overflow: hidden;
            background: #f3f4f6;
        }
        
        .theme-preview {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: top center;
            transition: transform 0.3s ease;
        }
        
        .theme-card:hover .theme-preview {
            transform: scale(1.05);
        }
        
        .bg-gradient {
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
        }
    </style>
    
    @yield('styles')
</head>
<body>
    <header>
        <div class="container header-content">
            <div class="logo">
                @if($settings->logo_url ?? null)
                    <a href="{{ url('/') }}">
                        <img src="{{ $settings->logo_url }}" alt="{{ $settings->logo_text ?? $settings->site_name ?? 'Rosetta CMS' }}" style="max-height: 32px; height: 32px; object-fit: contain;">
                    </a>
                @else
                    <a href="{{ url('/') }}">{{ $settings->logo_text ?? $settings->site_name ?? 'Rosetta CMS' }}</a>
                @endif
            </div>
            <nav>
                <ul>
                    <li><a href="{{ url('/') }}">Public Site</a></li>
                    <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" style="background: none; border: none; color: white; cursor: pointer; font-family: inherit;">Logout</button>
                        </form>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="admin-container">
        <div class="sidebar">
            <a href="{{ url('/dashboard') }}" class="{{ request()->is('dashboard') || request()->is('admin') ? 'active' : '' }}">Dashboard</a>
            
            <div class="sidebar-group">
                <a href="{{ url('/admin/posts') }}" class="{{ request()->is('admin/posts*') ? 'active' : '' }}">Posts</a>
                <div class="sidebar-submenu">
                    <a href="{{ url('/admin/categories') }}" class="{{ request()->is('admin/categories*') ? 'active' : '' }}">Categories</a>
                    <a href="{{ url('/admin/tags') }}" class="{{ request()->is('admin/tags*') ? 'active' : '' }}">Tags</a>
                    <a href="{{ url('/admin/comments') }}" class="{{ request()->is('admin/comments*') ? 'active' : '' }}">Comments</a>
                    <a href="{{ url('/admin/comments/pending') }}" class="{{ request()->is('admin/comments/pending') ? 'active' : '' }}">Pending</a>
                </div>
            </div>
            
            <a href="{{ url('/admin/users') }}" class="{{ request()->is('admin/users*') ? 'active' : '' }}">Users</a>
            <a href="{{ url('/admin/pages') }}" class="{{ request()->is('admin/pages*') ? 'active' : '' }}">Pages</a>
            <a href="{{ url('/admin/media') }}" class="{{ request()->is('admin/media*') ? 'active' : '' }}">Media</a>
            <a href="{{ url('/admin/menus') }}" class="{{ request()->is('admin/menus*') ? 'active' : '' }}">Menus</a>
            <a href="{{ url('/admin/sites') }}" class="{{ request()->is('admin/sites*') ? 'active' : '' }}">Sites</a>
            <a href="{{ url('/admin/themes') }}" class="{{ request()->is('admin/themes*') ? 'active' : '' }}">Themes</a>
            <a href="{{ url('/admin/plugins') }}" class="{{ request()->is('admin/plugins*') ? 'active' : '' }}">Plugins</a>
            <a href="{{ url('/admin/settings') }}" class="{{ request()->is('admin/settings*') ? 'active' : '' }}">Settings</a>
        </div>
        
        <div class="main-content">
            @yield('content')
        </div>
    </div>
    
    <div class="clearfix"></div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @stack('scripts')
</body>
</html>
