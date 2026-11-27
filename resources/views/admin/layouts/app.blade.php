@php
    // Ensure the layout always has settings available, even for views whose
    // controller does not explicitly pass them.
    $settings = $settings ?? \App\Models\SiteSetting::forSite();

    // Derive a sensible page title from the current route when the view
    // does not set one explicitly.
    if ($title ?? null) {
        $pageTitle = $title;
    } elseif (request()->routeIs('dashboard')) {
        $pageTitle = 'Dashboard';
    } else {
        $segment = \Illuminate\Support\Str::after(request()->route()?->getName() ?? '', 'admin.');
        $parts = explode('.', $segment);
        $pageTitle = $parts && $parts[0] ? \Illuminate\Support\Str::headline($parts[0]) : 'Admin Dashboard';
    }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        // Apply the saved theme before first paint to avoid a flash of the wrong theme.
        (function() {
            var saved = null;
            try { saved = localStorage.getItem('admin_theme'); } catch (e) {}
            if (!saved) saved = '{{ auth()->user()?->admin_theme ?? "light" }}';
            if (saved === 'dark') document.documentElement.setAttribute('data-theme', 'dark');
        })();
    </script>
    <title>{{ $pageTitle }} - Rosetta CMS</title>
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    
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
        
        [data-theme="dark"] {
            --bg-color: #141b2d;
            --text-color: #e6e9f0;
            --light-color: #1e2a45;
            --border-color: #2c3a5c;
            --dark-color: #0f172a;
            color-scheme: dark;
        }
        [data-theme="dark"] body {
            background-color: #141b2d;
            color: #e6e9f0;
        }
        [data-theme="dark"] .card,
        [data-theme="dark"] .widget,
        [data-theme="dark"] .list-group-item,
        [data-theme="dark"] .modal-content,
        [data-theme="dark"] .dropdown-menu,
        [data-theme="dark"] .alert,
        [data-theme="dark"] .table,
        [data-theme="dark"] .table-hover tbody tr:hover {
            background-color: #1e2a45;
            color: #e6e9f0;
            border-color: #2c3a5c;
        }
        [data-theme="dark"] .table th,
        [data-theme="dark"] .table thead th {
            background-color: #24304e;
            border-color: #2c3a5c;
            color: #c7cfdc;
        }
        [data-theme="dark"] .form-control,
        [data-theme="dark"] .form-select,
        [data-theme="dark"] .input-group-text,
        [data-theme="dark"] textarea,
        [data-theme="dark"] input[type="text"],
        [data-theme="dark"] input[type="search"],
        [data-theme="dark"] input[type="email"],
        [data-theme="dark"] input[type="password"],
        [data-theme="dark"] input[type="number"] {
            background-color: #141b2d;
            border-color: #2c3a5c;
            color: #e6e9f0;
        }
        [data-theme="dark"] .form-control::placeholder { color: #8a94a8; }
        [data-theme="dark"] .text-muted,
        [data-theme="dark"] .small.text-muted { color: #9aa5b8 !important; }
        [data-theme="dark"] .bg-light,
        [data-theme="dark"] .card-header.bg-transparent { background-color: #24304e; }
        [data-theme="dark"] .admin-search-results { background: #1e2a45; border-color: #2c3a5c; color: #e6e9f0; }
        [data-theme="dark"] .admin-search-results a { color: #e6e9f0; border-color: #2c3a5c; }
        [data-theme="dark"] .admin-search-results a:hover { background: #24304e; }
        [data-theme="dark"] .admin-search-results .result-type { background: #24304e; color: #9aa5b8; }
        [data-theme="dark"] .admin-search-results .no-results { color: #9aa5b8; }
        [data-theme="dark"] .hero,
        [data-theme="dark"] .hero * { color: #fff; }
        [data-theme="dark"] .skip-link { background: #e6e9f0; color: #141b2d; }

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
        
        .skip-link {
            position: absolute;
            top: -100px;
            left: 1rem;
            z-index: 10000;
            background: #111;
            color: #fff;
            padding: 0.5rem 1rem;
            border-radius: 0 0 6px 6px;
            text-decoration: none;
            transition: top 0.2s;
        }
        .skip-link:focus {
            top: 0;
            outline: 2px solid #fff;
        }

        :focus-visible {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
        }

        /* Admin Layout Container - avoid Bootstrap .container conflict */
        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            gap: 2rem;
        }
        
        .admin-container > .sidebar {
            flex: 0 0 220px;
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
            gap: 1rem;
            flex-wrap: wrap;
        }

        nav { display: flex; align-items: center; gap: 1rem; }
        
        .logo {
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        .logo a {
            color: white;
            text-decoration: none;
        }
        
        .admin-search { position: relative; display: flex; align-items: center; }
        .admin-search-input {
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.25);
            color: #fff;
            border-radius: 6px;
            padding: 0.4rem 0.75rem;
            font-size: 0.85rem;
            width: 260px;
            max-width: 34vw;
        }
        .admin-search-input::placeholder { color: rgba(255,255,255,0.6); }
        .admin-search-input:focus { outline: 2px solid #60a5fa; border-color: #60a5fa; }
        .admin-search-results {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            width: 340px;
            max-width: 88vw;
            background: #fff;
            color: #1f2937;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.18);
            z-index: 2000;
            max-height: 380px;
            overflow-y: auto;
        }
        .admin-search-results a {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            padding: 0.6rem 0.9rem;
            color: #1f2937;
            text-decoration: none;
            border-bottom: 1px solid #f3f4f6;
            font-size: 0.85rem;
        }
        .admin-search-results a:last-child { border-bottom: none; }
        .admin-search-results a:hover { background: #f8fafc; }
        .admin-search-results .result-type {
            flex-shrink: 0;
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #6b7280;
            background: #eef2f7;
            border-radius: 4px;
            padding: 0.1rem 0.35rem;
        }
        .admin-search-results .no-results { padding: 0.8rem 0.9rem; color: #6b7280; font-size: 0.85rem; }
        .notification-badge {
            position: absolute;
            top: 2px;
            right: 6px;
            background: #ef4444;
            color: #fff;
            font-size: 0.65rem;
            font-weight: 700;
            border-radius: 999px;
            min-width: 18px;
            text-align: center;
            padding: 0 4px;
            line-height: 1.5;
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
            gap: 0.25rem;
            width: 220px;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 0.5rem 0 1.5rem;
            z-index: 1000;
            scrollbar-width: thin;
        }
        .sidebar::-webkit-scrollbar { width: 6px; }
        .sidebar::-webkit-scrollbar-thumb { background: var(--border-color); border-radius: 3px; }

        .side-link {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.55rem 0.75rem;
            color: var(--text-color);
            text-decoration: none;
            border-radius: 6px;
            font-size: 0.9rem;
            transition: all 0.2s;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .side-link i {
            width: 1.1rem;
            text-align: center;
            flex: 0 0 1.1rem;
            opacity: 0.75;
            font-size: 0.95rem;
        }

        .side-link:hover {
            background-color: var(--light-color);
            color: var(--primary-color);
        }

        .side-link.active {
            background-color: var(--primary-color);
            color: white;
        }
        .side-link.active i { opacity: 1; }

        .side-group {
            margin-top: 0.35rem;
        }

        .side-group-toggle {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            width: 100%;
            padding: 0.55rem 0.75rem;
            background: none;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            color: var(--text-color);
            font-family: inherit;
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            transition: all 0.2s;
        }

        .side-group-toggle i:first-child {
            width: 1.1rem;
            text-align: center;
            flex: 0 0 1.1rem;
            opacity: 0.75;
            font-size: 0.9rem;
            text-transform: none;
        }

        .side-group-toggle:hover {
            background-color: var(--light-color);
            color: var(--primary-color);
        }

        .side-group-toggle .side-chevron {
            margin-left: auto;
            font-size: 0.7rem;
            opacity: 0.6;
            transition: transform 0.2s ease;
        }

        .side-group.open .side-group-toggle .side-chevron {
            transform: rotate(180deg);
        }

        .side-group-body {
            display: none;
            flex-direction: column;
            gap: 0.2rem;
            margin: 0.25rem 0 0.35rem;
            padding-left: 0.55rem;
            border-left: 2px solid var(--border-color);
        }

        .side-group.open .side-group-body { display: flex; }

        .side-sub {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            padding: 0.45rem 0.65rem;
            color: var(--text-color);
            text-decoration: none;
            border-radius: 6px;
            font-size: 0.88rem;
            transition: all 0.2s;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .side-sub i {
            width: 1.1rem;
            text-align: center;
            flex: 0 0 1.1rem;
            opacity: 0.65;
            font-size: 0.85rem;
        }

        .side-sub:hover {
            background-color: var(--light-color);
            color: var(--primary-color);
        }

        .side-sub.active {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
        }
        .side-sub.active i { opacity: 1; }

        .side-sub-sub {
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
            margin: -0.1rem 0 0.25rem 0.85rem;
            padding-left: 0.65rem;
            border-left: 1px solid var(--border-color);
        }

        .side-sub-sub a {
            padding: 0.3rem 0.55rem;
            color: var(--text-color);
            text-decoration: none;
            border-radius: 5px;
            font-size: 0.82rem;
            opacity: 0.85;
            transition: all 0.2s;
        }

        .side-sub-sub a:hover {
            background-color: var(--light-color);
            color: var(--primary-color);
            opacity: 1;
        }

        .side-sub-sub a.active {
            background-color: var(--light-color);
            color: var(--primary-color);
            font-weight: 600;
            opacity: 1;
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
        
        .card:not(.block-item):hover {
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

        /* --- Responsive: sidebar drawer, table scroll, layout collapse --- */
        .sidebar-toggle {
            display: none;
            align-items: center;
            justify-content: center;
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 6px;
            font-size: 1.15rem;
        }
        .sidebar-toggle:hover { background-color: rgba(255, 255, 255, 0.12); }
        .sidebar-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            z-index: 1150;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease;
        }
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border-radius: 8px;
        }
        .table-responsive table { width: 100%; }

        @media (max-width: 991px) {
            .admin-container {
                flex-direction: column;
                gap: 1rem;
                padding: 0 12px;
            }
            .admin-container > .sidebar {
                flex: none;
                width: 280px;
                max-width: 85vw;
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                background-color: var(--light-color);
                z-index: 1200;
                transform: translateX(-105%);
                transition: transform 0.25s ease;
                overflow-y: auto;
                box-shadow: 0 0 40px rgba(0, 0, 0, 0.25);
                padding: 1rem 0.75rem;
            }
            .admin-container > .sidebar.open { transform: translateX(0); }
            .sidebar-toggle { display: inline-flex; }
            .sidebar-backdrop { display: block; }
            .sidebar-backdrop.show { opacity: 1; pointer-events: auto; }
            .admin-container > .main-content {
                flex: none;
                order: 2;
                width: 100%;
                min-width: 0;
            }
            .table-responsive table { min-width: 640px; }
            header nav ul { gap: 0.25rem; }
            header nav a { padding: 0.5rem 0.6rem; font-size: 0.85rem; }
        }

        @media (max-width: 640px) {
            .admin-search { display: none; }
            .header-content { gap: 0.5rem; }
            .logo { font-size: 1.1rem; }
            main .table-responsive table { min-width: 560px; }
        }
    </style>
    
    @yield('styles')
</head>
<body>
    <a class="skip-link" href="#main-content">Skip to main content</a>
    <header>
        <div class="container header-content">
            <button type="button" id="sidebarToggle" class="sidebar-toggle" aria-label="Open menu" aria-expanded="false" aria-controls="adminSidebar">
                <i class="fas fa-bars"></i>
            </button>
            <div class="logo">
                @if($settings->logo_url ?? null)
                    <a href="{{ url('/') }}">
                        <img src="{{ $settings->logo_url }}" alt="{{ $settings->logo_text ?? $settings->site_title ?? ($site->name ?? 'Rosetta CMS') }}" style="max-height: 32px; height: 32px; object-fit: contain;">
                    </a>
                @else
                    <a href="{{ url('/') }}">{{ $settings->logo_text ?? $settings->site_title ?? ($site->name ?? 'Rosetta CMS') }}</a>
                @endif
            </div>
            <nav>
                <div class="admin-search">
                    <label for="adminSearchInput" class="visually-hidden">Search the admin</label>
                    <input type="search" id="adminSearchInput" class="admin-search-input" placeholder="Search posts, pages, media, users…" autocomplete="off" aria-label="Search the admin">
                    <div id="adminSearchResults" class="admin-search-results" hidden></div>
                </div>
                <ul>
                    <li><a href="{{ url('/') }}">Public Site</a></li>
                    <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    <li>
                        <a href="{{ route('admin.notifications.index') }}" id="notificationsBell" title="Notifications" aria-label="Notifications">
                            <i class="fas fa-bell"></i>
                            @php($notifCount = \App\Models\Comment::where('status', 'pending')->count() + \App\Models\Post::where('status', 'pending')->where('post_type', 'post')->count())
                            @if($notifCount > 0)
                                <span class="notification-badge">{{ $notifCount }}</span>
                            @endif
                        </a>
                    </li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" style="background: none; border: none; color: white; cursor: pointer; font-family: inherit;">Logout</button>
                        </form>
                    </li>
                    <li>
                        <button type="button" id="darkModeToggle" aria-pressed="false" aria-label="Toggle dark mode" style="background: none; border: none; color: white; cursor: pointer; font-family: inherit; padding: 0.5rem 1rem; border-radius: 4px; transition: background-color 0.2s;">
                            <i class="fas fa-moon" id="darkModeIcon" aria-hidden="true"></i>
                        </button>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <div class="admin-container">
        <div class="sidebar" id="adminSidebar">
            @php($currentUser = auth()->user())
            <a href="{{ url('/dashboard') }}" class="side-link {{ request()->is('dashboard') || request()->is('admin') ? 'active' : '' }}"><i class="fas fa-home"></i>Dashboard</a>
            <a href="{{ url('/profile') }}" class="side-link {{ request()->is('profile') ? 'active' : '' }}"><i class="fas fa-user"></i>Profile</a>
            <a href="{{ url('/admin/api-keys') }}" class="side-link {{ request()->is('admin/api-keys*') ? 'active' : '' }}"><i class="fas fa-key"></i>API Keys</a>
            <a href="{{ url('/api/v1/swagger') }}" target="_blank" rel="noopener" class="side-link {{ request()->is('api/v1/swagger*') ? 'active' : '' }}"><i class="fas fa-book"></i>API Docs</a>

            @if($currentUser?->isStaff())
            <div class="side-group">
                <button type="button" class="side-group-toggle" aria-expanded="false"><i class="fas fa-folder-open"></i>Content<i class="fas fa-chevron-down side-chevron"></i></button>
                <div class="side-group-body">
                    <a href="{{ url('/admin/posts') }}" class="side-sub {{ request()->is('admin/posts*') || request()->is('admin/categories*') || request()->is('admin/tags*') || request()->is('admin/comments*') ? 'active' : '' }}"><i class="fas fa-newspaper"></i>Posts</a>
                    <div class="side-sub-sub">
                        <a href="{{ url('/admin/categories') }}" class="{{ request()->is('admin/categories*') ? 'active' : '' }}">Categories</a>
                        <a href="{{ url('/admin/tags') }}" class="{{ request()->is('admin/tags*') ? 'active' : '' }}">Tags</a>
                        <a href="{{ url('/admin/comments') }}" class="{{ request()->is('admin/comments*') && !request()->is('admin/comments/pending') ? 'active' : '' }}">Comments</a>
                        <a href="{{ url('/admin/comments/pending') }}" class="{{ request()->is('admin/comments/pending') ? 'active' : '' }}">Pending</a>
                    </div>
                    <a href="{{ url('/admin/pages') }}" class="side-sub {{ request()->is('admin/pages*') ? 'active' : '' }}"><i class="fas fa-file"></i>Pages</a>
                    <a href="{{ url('/admin/reusable-blocks') }}" class="side-sub {{ request()->is('admin/reusable-blocks*') ? 'active' : '' }}"><i class="fas fa-puzzle-piece"></i>Reusable Blocks</a>
                    <a href="{{ url('/admin/media') }}" class="side-sub {{ request()->is('admin/media*') ? 'active' : '' }}"><i class="fas fa-images"></i>Media</a>
                    <a href="{{ url('/admin/menus') }}" class="side-sub {{ request()->is('admin/menus*') ? 'active' : '' }}"><i class="fas fa-list"></i>Menus</a>
                    <a href="{{ url('/admin/forms') }}" class="side-sub {{ request()->is('admin/forms*') && !request()->is('admin/forms-builder*') ? 'active' : '' }}"><i class="fas fa-clipboard-list"></i>Forms</a>
                    <a href="{{ url('/admin/forms-builder') }}" class="side-sub {{ request()->is('admin/forms-builder*') ? 'active' : '' }}"><i class="fas fa-file-signature"></i>Form Builder</a>
                    <a href="{{ url('/admin/bookings') }}" class="side-sub {{ request()->is('admin/bookings*') ? 'active' : '' }}"><i class="fas fa-calendar-check"></i>Bookings</a>
                    @php($chatUnread = \App\Models\ChatMessage::where('sender', 'visitor')->whereNull('read_at')->count())
                    <a href="{{ url('/admin/chat') }}" class="side-sub {{ request()->is('admin/chat*') ? 'active' : '' }}"><i class="fas fa-comments"></i>Chat @if($chatUnread > 0) <span style="display: inline-block; min-width: 20px; padding: 0 6px; margin-left: auto; border-radius: 999px; background-color: #ef4444; color: white; font-size: 0.7rem; font-weight: 700; text-align: center;">{{ $chatUnread > 99 ? '99+' : $chatUnread }}</span>@endif</a>
                    @foreach(\App\Support\PostTypes::generic() as $typeSlug => $typeDef)
                        <a href="{{ url('/admin/' . $typeSlug) }}" class="side-sub {{ request()->is('admin/' . $typeSlug . '*') && !request()->is('admin/event/calendar*') ? 'active' : '' }}"><i class="{{ $typeDef['icon'] ?? 'fas fa-box' }}"></i>{{ $typeDef['menu'] }}</a>
                    @endforeach
                    <a href="{{ url('/admin/event/calendar') }}" class="side-sub {{ request()->is('admin/event/calendar*') ? 'active' : '' }}"><i class="fas fa-calendar"></i>Event Calendar</a>
                </div>
            </div>

            @if($currentUser?->hasRole('admin'))
            <div class="side-group">
                <button type="button" class="side-group-toggle" aria-expanded="false"><i class="fas fa-server"></i>System<i class="fas fa-chevron-down side-chevron"></i></button>
                <div class="side-group-body">
                    <a href="{{ url('/admin/users') }}" class="side-sub {{ request()->is('admin/users*') ? 'active' : '' }}"><i class="fas fa-users"></i>Users</a>
                    <a href="{{ url('/admin/roles') }}" class="side-sub {{ request()->is('admin/roles*') ? 'active' : '' }}"><i class="fas fa-user-shield"></i>Roles</a>
                    <a href="{{ url('/admin/sites') }}" class="side-sub {{ request()->is('admin/sites*') ? 'active' : '' }}"><i class="fas fa-globe"></i>Sites</a>
                    <a href="{{ url('/admin/languages') }}" class="side-sub {{ request()->is('admin/languages*') ? 'active' : '' }}"><i class="fas fa-language"></i>Languages</a>
                    <a href="{{ url('/admin/translations') }}" class="side-sub {{ request()->is('admin/translations*') ? 'active' : '' }}"><i class="fas fa-exchange-alt"></i>Translations</a>
                </div>
            </div>

            <div class="side-group">
                <button type="button" class="side-group-toggle" aria-expanded="false"><i class="fas fa-shopping-cart"></i>Commerce<i class="fas fa-chevron-down side-chevron"></i></button>
                <div class="side-group-body">
                    <a href="{{ url('/admin/products') }}" class="side-sub {{ request()->is('admin/products*') ? 'active' : '' }}"><i class="fas fa-box"></i>Products</a>
                    <a href="{{ url('/admin/orders') }}" class="side-sub {{ request()->is('admin/orders*') ? 'active' : '' }}"><i class="fas fa-receipt"></i>Orders</a>
                    <a href="{{ url('/admin/coupons') }}" class="side-sub {{ request()->is('admin/coupons*') ? 'active' : '' }}"><i class="fas fa-ticket-alt"></i>Coupons</a>
                </div>
            </div>

            <div class="side-group">
                <button type="button" class="side-group-toggle" aria-expanded="false"><i class="fas fa-wrench"></i>Admin<i class="fas fa-chevron-down side-chevron"></i></button>
                <div class="side-group-body">
                    <a href="{{ url('/admin/themes') }}" class="side-sub {{ request()->is('admin/themes*') ? 'active' : '' }}"><i class="fas fa-palette"></i>Themes</a>
                    <a href="{{ url('/admin/plugins') }}" class="side-sub {{ request()->is('admin/plugins*') ? 'active' : '' }}"><i class="fas fa-plug"></i>Plugins</a>
                    <a href="{{ url('/admin/backups') }}" class="side-sub {{ request()->is('admin/backups*') ? 'active' : '' }}"><i class="fas fa-database"></i>Backups</a>
                    <a href="{{ url('/admin/webhooks') }}" class="side-sub {{ request()->is('admin/webhooks*') ? 'active' : '' }}"><i class="fas fa-webhook"></i>Webhooks</a>
                    <a href="{{ url('/admin/activity') }}" class="side-sub {{ request()->is('admin/activity*') ? 'active' : '' }}"><i class="fas fa-history"></i>Activity Log</a>
                    <a href="{{ url('/admin/analytics') }}" class="side-sub {{ request()->is('admin/analytics*') ? 'active' : '' }}"><i class="fas fa-chart-bar"></i>Analytics</a>
                    <a href="{{ url('/admin/health') }}" class="side-sub {{ request()->is('admin/health*') ? 'active' : '' }}"><i class="fas fa-heartbeat"></i>Health</a>
                    <a href="{{ url('/admin/ip-rules') }}" class="side-sub {{ request()->is('admin/ip-rules*') ? 'active' : '' }}"><i class="fas fa-filter"></i>IP Rules</a>
                    <a href="{{ url('/admin/redirects') }}" class="side-sub {{ request()->is('admin/redirects*') ? 'active' : '' }}"><i class="fas fa-external-link-alt"></i>Redirects</a>
                    <a href="{{ url('/admin/seo-health') }}" class="side-sub {{ request()->is('admin/seo-health*') ? 'active' : '' }}"><i class="fas fa-search"></i>SEO Health</a>
                    <a href="{{ url('/admin/settings') }}" class="side-sub {{ request()->is('admin/settings*') ? 'active' : '' }}"><i class="fas fa-cog"></i>Settings</a>
                </div>
            </div>
            @endif
            @endif
        </div>
        
        <main class="main-content" id="main-content" tabindex="-1">
            @yield('content')
        </main>
    </div>
    
    <div class="clearfix"></div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Alpine.js for interactive admin widgets -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js" defer></script>

    @stack('scripts')
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- Accessibility: mark the current page in the sidebar/nav ---
            document.querySelectorAll('.sidebar a.active, nav a.active').forEach(function(a) {
                a.setAttribute('aria-current', 'page');
            });

            // --- Mobile sidebar drawer ---
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('adminSidebar');
            const backdrop = document.getElementById('sidebarBackdrop');

            function setSidebarOpen(open) {
                if (!sidebar || !backdrop) return;
                sidebar.classList.toggle('open', open);
                backdrop.classList.toggle('show', open);
                if (sidebarToggle) {
                    sidebarToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                    sidebarToggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
                }
                document.body.style.overflow = open ? 'hidden' : '';
            }

            if (sidebarToggle && sidebar) {
                sidebarToggle.addEventListener('click', function() {
                    setSidebarOpen(!sidebar.classList.contains('open'));
                });
                backdrop.addEventListener('click', function() { setSidebarOpen(false); });
                sidebar.addEventListener('click', function(e) {
                    if (e.target.closest('a')) setSidebarOpen(false);
                });
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && sidebar.classList.contains('open')) setSidebarOpen(false);
                });
            }

            // --- Collapsible sidebar groups ---
            const sideGroups = document.querySelectorAll('.side-group');
            sideGroups.forEach(function(group) {
                const toggle = group.querySelector('.side-group-toggle');
                const links = group.querySelectorAll('.side-sub, .side-sub-sub a');
                if (!toggle) return;
                // Auto-open the group that contains the active link
                const hasActive = Array.from(links).some(function(a) { return a.classList.contains('active'); });
                if (hasActive) group.classList.add('open');
                toggle.addEventListener('click', function() {
                    const isOpen = group.classList.toggle('open');
                    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                });
                toggle.setAttribute('aria-expanded', group.classList.contains('open') ? 'true' : 'false');
            });

            // --- Dark mode toggle (persisted per user) ---
            const darkModeToggle = document.getElementById('darkModeToggle');
            const darkModeIcon = document.getElementById('darkModeIcon');
            const htmlElement = document.documentElement;

            function renderTheme(theme) {
                if (theme === 'dark') {
                    htmlElement.setAttribute('data-theme', 'dark');
                    darkModeIcon.classList.remove('fa-moon');
                    darkModeIcon.classList.add('fa-sun');
                    darkModeToggle.setAttribute('aria-pressed', 'true');
                    darkModeToggle.setAttribute('aria-label', 'Switch to light mode');
                } else {
                    htmlElement.removeAttribute('data-theme');
                    darkModeIcon.classList.remove('fa-sun');
                    darkModeIcon.classList.add('fa-moon');
                    darkModeToggle.setAttribute('aria-pressed', 'false');
                    darkModeToggle.setAttribute('aria-label', 'Switch to dark mode');
                }
            }

            const currentTheme = htmlElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
            renderTheme(currentTheme);

            darkModeToggle.addEventListener('click', function() {
                const next = htmlElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
                renderTheme(next);
                try { localStorage.setItem('admin_theme', next); } catch (e) {}

                fetch('{{ route('admin.preferences.theme') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                    },
                    body: JSON.stringify({ theme: next }),
                }).catch(function() {});
            });

            // --- Admin global search ---
            const searchInput = document.getElementById('adminSearchInput');
            const searchResults = document.getElementById('adminSearchResults');
            if (searchInput && searchResults) {
                let activeIndex = -1;
                let debounce = null;

                function closeResults() {
                    searchResults.hidden = true;
                    searchResults.innerHTML = '';
                    activeIndex = -1;
                }

                function renderResults(items) {
                    searchResults.textContent = '';
                    if (!items || items.length === 0) {
                        const empty = document.createElement('div');
                        empty.className = 'no-results';
                        empty.textContent = 'No results found.';
                        searchResults.appendChild(empty);
                    } else {
                        items.forEach(function(item, i) {
                            const a = document.createElement('a');
                            a.href = item.url;
                            a.setAttribute('role', 'option');
                            a.setAttribute('data-index', String(i));
                            const type = document.createElement('span');
                            type.className = 'result-type';
                            type.textContent = item.type;
                            const label = document.createElement('span');
                            label.textContent = item.label;
                            a.appendChild(type);
                            a.appendChild(label);
                            searchResults.appendChild(a);
                        });
                    }
                    searchResults.hidden = false;
                    activeIndex = -1;
                }

                function moveActive(delta) {
                    const links = searchResults.querySelectorAll('a[role="option"]');
                    if (links.length === 0) return;
                    activeIndex = (activeIndex + delta + links.length) % links.length;
                    links.forEach(function(l, i) { l.style.background = i === activeIndex ? '#eef2ff' : ''; });
                    links[activeIndex].scrollIntoView({ block: 'nearest' });
                }

                function currentActiveLink() {
                    return searchResults.querySelectorAll('a[role="option"]')[activeIndex];
                }

                searchInput.addEventListener('input', function() {
                    clearTimeout(debounce);
                    const q = searchInput.value.trim();
                    if (q.length < 2) { closeResults(); return; }
                    debounce = setTimeout(function() {
                        fetch('{{ route('admin.search') }}?q=' + encodeURIComponent(q))
                            .then(function(r) { return r.json(); })
                            .then(function(data) { renderResults(data.results || []); })
                            .catch(closeResults);
                    }, 200);
                });

                searchInput.addEventListener('keydown', function(e) {
                    if (e.key === 'ArrowDown') { e.preventDefault(); moveActive(1); }
                    else if (e.key === 'ArrowUp') { e.preventDefault(); moveActive(-1); }
                    else if (e.key === 'Enter') {
                        const link = currentActiveLink();
                        if (link) { e.preventDefault(); window.location = link.href; }
                    } else if (e.key === 'Escape') { closeResults(); searchInput.blur(); }
                });

                searchInput.addEventListener('focus', function() {
                    if (searchResults.innerHTML.trim()) searchResults.hidden = false;
                });

                document.addEventListener('click', function(e) {
                    if (!e.target.closest('.admin-search')) closeResults();
                });
            }
        });
    </script>
</body>
</html>
