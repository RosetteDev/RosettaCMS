# Rosetta CMS Theme Development Guide

A comprehensive guide for creating custom themes for Rosetta CMS.

## Table of Contents

- [Overview](#overview)
- [Theme Structure](#theme-structure)
- [Creating a New Theme](#creating-a-new-theme)
- [Theme Configuration (theme.json)](#theme-configuration-themefile)
- [Theme Views](#theme-views)
- [CSS & JavaScript Assets](#css--javascript-assets)
- [Working with Theme Options](#working-with-theme-options)
- [Available Shared Components](#available-shared-components)
- [Theme Development Best Practices](#theme-development-best-practices)
- [Creating Theme Screenshots](#creating-theme-screenshots)
- [Publishing Your Theme](#publishing-your-theme)

---

## Overview

Rosetta CMS themes are built using Laravel's Blade templating engine and follow a structure similar to WordPress themes but with modern Laravel conventions. Themes control the presentation layer of your website, separating content from design.

### Key Features

- **Blade Templates**: Use Laravel's powerful Blade templating engine
- **Theme Options**: Customize colors, fonts, and layout through theme settings
- **Responsive Design**: Build mobile-friendly themes with CSS variables
- **Widget Support**: Integrate sidebar widgets and social feeds
- **SEO Ready**: Built-in SEO and Open Graph support

---

## Theme Structure

A typical Rosetta CMS theme follows this structure:

```
themes/
└── your-theme-name/
    ├── theme.json              # Theme configuration
    ├── screenshot.png          # Theme preview image (760x500px)
    ├── css/
    │   └── style.css           # Main stylesheet
    ├── js/
    │   └── main.js             # JavaScript file
    └── views/
        ├── layouts/
        │   └── app.blade.php   # Main layout template
        ├── pages/
        │   ├── home.blade.php  # Home page
        │   ├── about.blade.php # About page
        │   ├── contact.blade.php # Contact page
        │   └── single.blade.php # Default page template
        ├── posts/
        │   ├── single.blade.php # Blog post
        │   ├── archive.blade.php # Post archive
        │   ├── category.blade.php # Category archive
        │   └── tag.blade.php # Tag archive
        ├── partials/
        │   ├── header.blade.php # Header partial
        │   ├── footer.blade.php # Footer partial
        │   ├── navigation.blade.php # Navigation menu
        │   └── sidebar.blade.php # Sidebar widget area
        ├── searches/
        │   └── results.blade.php # Search results
        ├── archives/
        │   └── date.blade.php # Date-based archive
        ├── 404.blade.php        # Error page
        └── search.blade.php     # Search page
```

---

## Creating a New Theme

### Step 1: Create Theme Directory

Create a new directory in the `themes/` folder with your theme's slug (lowercase, no spaces):

```bash
mkdir themes/my-awesome-theme
```

### Step 2: Create theme.json

Create the essential `theme.json` file:

```json
{
    "name": "My Awesome Theme",
    "slug": "my-awesome-theme",
    "version": "1.0.0",
    "description": "A beautiful, modern theme for Rosetta CMS",
    "author": "Your Name",
    "author_url": "https://yourwebsite.com",
    "screenshot": "screenshot.png",
    "preview_url": null,
    "supports": {
        "features": ["menu", "widgets", "customizer", "thumbnails"],
        "layouts": ["one-column", "two-column", "three-column"],
        "post_types": ["post", "page"]
    },
    "settings": {
        "primary_color": "#3b82f6",
        "secondary_color": "#6b7280",
        "accent_color": "#10b981",
        "background_color": "#ffffff",
        "text_color": "#1f2937",
        "font_family": "system-ui, sans-serif",
        "container_width": "1200px"
    },
    "assets": {
        "css": ["css/style.css"],
        "js": ["js/main.js"]
    },
    "views": {
        "layouts": ["layouts/app"],
        "pages": ["pages/home", "pages/about", "pages/contact", "pages/single"],
        "posts": ["posts/single", "posts/archive", "posts/category", "posts/tag"],
        "partials": ["partials/header", "partials/footer", "partials/navigation", "partials/sidebar"]
    }
}
```

### Step 3: Create CSS Directory and Stylesheet

```bash
mkdir -p themes/my-awesome-theme/css
```

Create `css/style.css`:

```css
/* My Awesome Theme - Stylesheet */

/* Base Styles */
:root {
    --primary-color: #3b82f6;
    --secondary-color: #6b7280;
    --accent-color: #10b981;
    --bg-color: #ffffff;
    --text-color: #1f2937;
}

/* Typography */
body {
    font-family: system-ui, -apple-system, sans-serif;
    line-height: 1.6;
    margin: 0;
    padding: 0;
    background-color: var(--bg-color);
    color: var(--text-color);
}

/* Container */
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1.5rem;
}

/* Header */
.header {
    background: white;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    padding: 1rem 0;
}

/* Navigation */
.nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* Content */
.content {
    padding: 4rem 0;
}

/* Footer */
.footer {
    background: var(--secondary-color);
    color: white;
    padding: 3rem 0;
    margin-top: 4rem;
}
```

### Step 4: Create JavaScript File

```bash
mkdir -p themes/my-awesome-theme/js
```

Create `js/main.js`:

```javascript
// My Awesome Theme - Main JavaScript

document.addEventListener('DOMContentLoaded', function() {
    console.log('My Awesome Theme loaded!');
    
    // Add your custom JavaScript here
    // Mobile menu toggle, form enhancements, etc.
});

// Example: Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});
```

### Step 5: Create Layout Template

Create `views/layouts/app.blade.php`:

```blade
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('themes._shared.seo')
    
    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('themes/my-awesome-theme/css/style.css') }}">
    @stack('styles')
    
    <style>
        :root {
            --primary-color: {{ $theme_options['primary_color'] ?? '#3b82f6' }};
            --secondary-color: {{ $theme_options['secondary_color'] ?? '#6b7280' }};
            --accent-color: {{ $theme_options['accent_color'] ?? '#10b981' }};
            --bg-color: {{ $theme_options['background_color'] ?? '#ffffff' }};
            --text-color: {{ $theme_options['text_color'] ?? '#1f2937' }};
            --font-family: {{ $theme_options['font_family'] ?? 'system-ui, sans-serif' }};
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="header">
        <div class="container nav">
            <a href="{{ url('/') }}" class="nav-logo">
                {{ $settings->site_title ?? 'My Site' }}
            </a>
            
            @include('themes._shared.nav', ['menu' => $headerMenu ?? null])
        </div>
    </nav>

    <!-- Main Content -->
    <main class="content">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer">
        @include('themes._shared.footer')
    </footer>
    
    @stack('scripts')
</body>
</html>
```

### Step 6: Create Home Page

Create `views/pages/home.blade.php`:

```blade
@extends('themes.my-awesome-theme.views.layouts.app')

@section('content')
<div class="hero">
    <div class="container">
        <h1>Welcome to {{ $site->name ?? 'My Site' }}</h1>
        <p>A modern, flexible content management system built with Laravel</p>
        
        <div style="margin-top: 2rem;">
            <a href="{{ route('admin.login') }}" class="btn btn-primary">Get Started</a>
            <a href="{{ url('/blog') }}" class="btn btn-outline">Read Our Blog</a>
        </div>
    </div>
</div>

<div class="container" style="padding: 4rem 0;">
    <h2>Latest Posts</h2>
    
    @if($posts->count() > 0)
        <div class="posts-grid">
            @foreach($posts->take(3) as $post)
                <article class="post-card">
                    <h3><a href="{{ route('post.show', $post->slug) }}">{{ $post->title }}</a></h3>
                    <p>{{ Str::limit($post->content, 150) }}</p>
                    <a href="{{ route('post.show', $post->slug) }}" class="btn btn-primary">Read More</a>
                </article>
            @endforeach
        </div>
    @else
        <p>No posts available yet.</p>
    @endif
</div>
@endsection
```

---

## Theme Configuration (theme.json)

The `theme.json` file is the heart of your theme configuration. Here's what each field does:

### Required Fields

| Field | Description |
|-------|-------------|
| `name` | Display name of your theme |
| `slug` | Unique slug (lowercase, no spaces, hyphens allowed) |
| `version` | Version number (e.g., "1.0.0") |
| `description` | Brief description of your theme |
| `author` | Theme author name |
| `author_url` | URL to author's website |
| `screenshot` | Filename of theme preview image |
| `supports` | Features your theme supports |
| `settings` | Theme customization options |
| `assets` | CSS and JS asset paths |
| `views` | Available view templates |

### Supports Configuration

```json
"supports": {
    "features": ["menu", "widgets", "customizer", "thumbnails"],
    "layouts": ["one-column", "two-column", "sidebar-left", "sidebar-right"],
    "post_types": ["post", "page"]
}
```

**Features:**
- `menu` - Navigation menu support
- `widgets` - Sidebar widget areas
- `customizer` - Theme customizer support
- `thumbnails` - Featured image support

**Layouts:**
- `one-column` - Single column layout
- `two-column` - Two column layout
- `sidebar-left` - Sidebar on left
- `sidebar-right` - Sidebar on right

### Settings Configuration

Theme settings are exposed as CSS variables in your templates. Users can customize these through the theme customizer.

```json
"settings": {
    "primary_color": "#3b82f6",
    "secondary_color": "#6b7280",
    "accent_color": "#10b981",
    "background_color": "#ffffff",
    "text_color": "#1f2937",
    "font_family": "system-ui, sans-serif",
    "container_width": "1200px",
    "header_background": "#ffffff",
    "footer_background": "#1f2937"
}
```

---

## Theme Views

### View Type Hierarchy

Rosetta CMS uses a hierarchy system for rendering different page types:

1. **Specific templates** (if they exist)
2. **Generic templates** (fallbacks)
3. **Layout template** (final fallback)

#### Pages

| View File | Purpose |
|-----------|---------|
| `views/pages/home.blade.php` | Homepage |
| `views/pages/about.blade.php` | About page |
| `views/pages/contact.blade.php` | Contact page |
| `views/pages/single.blade.php` | Default page template |

#### Posts

| View File | Purpose |
|-----------|---------|
| `views/posts/single.blade.php` | Individual blog post |
| `views/posts/archive.blade.php` | Blog archive |
| `views/posts/category.blade.php` | Category archive |
| `views/posts/tag.blade.php` | Tag archive |

#### Other Templates

| View File | Purpose |
|-----------|---------|
| `views/404.blade.php` | 404 error page |
| `views/search.blade.php` | Search results |
| `views/archive.blade.php` | Date archive |

### View Variables

Available variables in your theme views:

| Variable | Type | Description |
|----------|------|-------------|
| `$site` | Object | Site model |
| `$settings` | Object | Site settings |
| `$posts` | Collection | Posts collection |
| `$post` | Object | Single post (in post views) |
| `$theme_options` | Array | Theme settings |
| `$headerMenu` | Object | Header navigation menu |
| `$footerMenu` | Object | Footer navigation menu |
| `$categories` | Collection | Post categories |
| `$tags` | Collection | Post tags |
| `$recentPosts` | Collection | Recent posts |
| `$seo` | Array | SEO data |

---

## CSS & JavaScript Assets

### CSS Organization

```css
/* 1. CSS Variables (Theme Options) */
:root {
    --primary-color: #3b82f6;
    --secondary-color: #6b7280;
    /* ... */
}

/* 2. Reset & Base Styles */
* { box-sizing: border-box; }
body { margin: 0; font-family: var(--font-family); }

/* 3. Layout Styles */
.container { max-width: 1200px; margin: 0 auto; }

/* 4. Component Styles */
.nav { /* ... */ }
.card { /* ... */ }
.btn { /* ... */ }

/* 5. Page-Specific Styles */
.hero { /* ... */ }
.footer { /* ... */ }

/* 6. Responsive Styles */
@media (max-width: 768px) { /* ... */ }
```

### JavaScript Organization

```javascript
// 1. Document Ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize theme
    Theme.init();
});

// 2. Theme Object
const Theme = {
    init: function() {
        this.setupMobileMenu();
        this.setupSmoothScroll();
        this.setupForms();
    },
    
    setupMobileMenu: function() {
        // Mobile menu toggle logic
    },
    
    setupSmoothScroll: function() {
        // Smooth scroll for anchor links
    },
    
    setupForms: function() {
        // Form validation and submission
    }
};
```

### Using @stack for Asset Management

In your layout template:

```blade
<head>
    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('themes/your-theme/css/style.css') }}">
    @stack('styles')
</head>

<body>
    <!-- Content -->
    @yield('content')
    
    @stack('scripts')
</body>
```

In your page templates:

```blade
@push('styles')
<style>
    /* Page-specific styles */
    .hero { background: #3b82f6; }
</style>
@endpush

@push('scripts')
<script>
    // Page-specific JavaScript
    console.log('Page loaded!');
</script>
@endpush
```

---

## Working with Theme Options

Theme options are configured in `theme.json` and can be customized by users through the theme customizer. The settings are exposed as CSS variables.

### Accessing Theme Options in Blade

```blade
<style>
    :root {
        --primary-color: {{ $theme_options['primary_color'] ?? '#3b82f6' }};
        --secondary-color: {{ $theme_options['secondary_color'] ?? '#6b7280' }};
        --container-width: {{ $theme_options['container_width'] ?? '1200px' }};
    }
</style>

<div style="max-width: var(--container-width);">
    <p style="color: var(--primary-color);">Hello World</p>
</div>
```

### Using Theme Options in CSS

```css
/* css/style.css */

:root {
    --primary-color: #3b82f6;
    --secondary-color: #6b7280;
}

.btn {
    background-color: var(--primary-color);
    color: white;
}

.btn:hover {
    background-color: var(--secondary-color);
}
```

### Customizer Integration

When users change theme options through the customizer:

1. Changes are saved to `config/themes/{theme-slug}.php`
2. Options are available as `$theme_options` array
3. CSS variables are updated automatically
4. No cache clear required for CSS variable changes

---

## Available Shared Components

Rosetta CMS provides shared Blade components for common elements.

### SEO Component

```blade
@include('themes._shared.seo')
```

This includes:
- Page title
- Meta description
- Keywords
- Open Graph tags
- Twitter Card tags
- Favicon link
- Sitemap and RSS feed links
- JSON-LD structured data

### Navigation Component

```blade
@include('themes._shared.nav', ['menu' => $headerMenu ?? null, 'navClass' => 'nav-menu'])
```

**Parameters:**
- `menu` - Navigation menu object (optional)
- `navClass` - CSS class for the nav element (optional)

### Footer Component

```blade
@include('themes._shared.footer')
```

Includes:
- Site name and description
- Footer menu links
- Contact information
- Copyright notice

### Breadcrumbs Component

```blade
@include('themes._shared.breadcrumbs')
```

Generates dynamic breadcrumbs based on current URL.

### Sidebar Component

Example sidebar with widgets:

```blade
<div class="sidebar">
    <!-- Search Widget -->
    <div class="sidebar-widget">
        <h5>Search</h5>
        <form action="{{ url('/search') }}" method="GET">
            <input type="text" name="q" placeholder="Search...">
            <button type="submit">Search</button>
        </form>
    </div>

    <!-- Categories Widget -->
    <div class="sidebar-widget">
        <h5>Categories</h5>
        <ul>
            @foreach($categories as $category)
                <li>
                    <a href="{{ url('/categories/' . $category->slug) }}">
                        {{ $category->name }}
                        <span>({{ $category->posts_count ?? 0 }})</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>

    <!-- Recent Posts Widget -->
    <div class="sidebar-widget">
        <h5>Recent Posts</h5>
        <ul>
            @foreach($recentPosts as $post)
                <li>
                    <a href="{{ route('post.show', $post->slug) }}">
                        {{ $post->title }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</div>
```

---

## Theme Development Best Practices

### 1. Follow Semantic HTML

```blade
<!-- Good -->
<header class="site-header">...</header>
<nav class="primary-nav">...</nav>
<main class="site-content">...</main>
<footer class="site-footer">...</footer>

<!-- Bad -->
<div class="header">...</div>
<div class="menu">...</div>
<div class="content">...</div>
<div class="footer">...</div>
```

### 2. Use CSS Variables for Theming

```css
/* Good - Uses CSS variables */
:root {
    --primary-color: #3b82f6;
}
.btn {
    background-color: var(--primary-color);
}

/* Bad - Hardcoded colors */
.btn {
    background-color: #3b82f6;
}
```

### 3. Mobile-First Design

```css
/* Good - Mobile first */
.btn {
    padding: 0.75rem 1.5rem;
    width: 100%;
}

@media (min-width: 768px) {
    .btn {
        width: auto;
        display: inline-block;
    }
}
```

### 4. Optimize Images

```blade
<!-- Good - Responsive images -->
<img 
    src="{{ $post->featured_image->url }}" 
    alt="{{ $post->title }}"
    loading="lazy"
    width="800"
    height="450"
>

<!-- Bad - Unoptimized -->
<img src="{{ $post->featured_image->url }}" alt="{{ $post->title }}">
```

### 5. Accessibility

```blade
<!-- Good - Accessible -->
<nav aria-label="Main navigation">
    <ul class="nav-menu">
        <li><a href="{{ url('/') }}" aria-current="page">Home</a></li>
    </ul>
</nav>

<!-- Bad - Not accessible -->
<div class="nav">
    <ul>
        <li><a href="{{ url('/') }}">Home</a></li>
    </ul>
</div>
```

### 6. Performance

```blade
<!-- Good - Optimized assets -->
<link rel="stylesheet" href="{{ asset('themes/my-theme/css/style.css') }}" media="all">
@stack('styles')

@stack('scripts')
```

```javascript
// Defer JavaScript
<script src="{{ asset('themes/my-theme/js/main.js') }}" defer></script>
```

---

## Creating Theme Screenshots

### Requirements

- **Dimensions**: 760px × 500px
- **Format**: PNG or JPG
- **Content**: Full website layout with sample content
- **Background**: White or transparent

### Tips

1. **Show Key Features**: Display header, navigation, content area, and sidebar
2. **Include Widgets**: Show common widgets if your theme supports them
3. ** representative**: Use content that represents typical website content
4. **Branding**: Include your theme's branding subtly
5. **Test Multiple Resolutions**: Show responsive behavior if possible

### Example Screenshot Layout

```
┌─────────────────────────────────────────┐
│  Header with Logo & Navigation          │
├─────────────────────────────────────────┤
│  Hero Section with Call-to-Action       │
├────────────────────┬────────────────────┤
│                    │                    │
│  Main Content      │    Sidebar         │
│  (Blog Posts)      │  - Search Widget   │
│                    │  - Categories      │
│                    │  - Recent Posts    │
├────────────────────┴────────────────────┤
│  Footer with Copyright & Links          │
└─────────────────────────────────────────┘
```

---

## Publishing Your Theme

### 1. Theme Checklist

Before publishing, verify:

- [ ] `theme.json` is valid JSON
- [ ] All template files exist
- [ ] CSS is optimized and minified
- [ ] JavaScript is optimized and minified
- [ ] Screenshot meets requirements
- [ ] Theme works with latest Rosetta CMS version
- [ ] Theme is responsive (mobile-friendly)
- [ ] Theme supports all required features
- [ ] No hardcoded paths or credentials
- [ ] Theme follows WordPress Theme Review Guidelines

### 2. Testing Your Theme

```bash
# Clear Laravel cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Test theme rendering
php artisan route:list
php artisan theme:preview your-theme-name
```

### 3. Documentation

Include a `README.md` with:

```markdown
# Your Theme Name

A beautiful, modern theme for Rosetta CMS.

## Features

- Responsive design
- Theme customizer support
- Widget areas
- SEO optimized
- Fast performance

## Installation

1. Upload to `themes/your-theme-name/`
2. Activate in Admin > Appearance > Themes
3. Configure theme settings in Admin > Appearance > Customize

## Configuration

Edit `config/themes/your-theme-name.php` to customize settings.

## Support

For issues and questions, please visit [GitHub Issues](https://github.com/your/repo/issues).

## Changelog

### 1.0.0
- Initial release
```

### 4. Version Control

Use semantic versioning:

```
MAJOR.MINOR.PATCH

1.0.0 → 1.0.1 → 1.1.0 → 2.0.0
```

- **MAJOR**: Breaking changes
- **MINOR**: New features (backward compatible)
- **PATCH**: Bug fixes (backward compatible)

---

## Quick Start Template

For the fastest start, use this minimal theme structure:

### Directory Structure

```
themes/
└── quickstart/
    ├── theme.json
    ├── css/
    │   └── style.css
    └── views/
        ├── layouts/
        │   └── app.blade.php
        └── pages/
            └── home.blade.php
```

### theme.json

```json
{
    "name": "Quick Start",
    "slug": "quickstart",
    "version": "1.0.0",
    "description": "A simple starting point for theme development",
    "author": "Your Name",
    "author_url": "https://yourwebsite.com",
    "screenshot": "screenshot.png",
    "supports": {
        "features": ["menu", "widgets"],
        "layouts": ["one-column"],
        "post_types": ["post", "page"]
    },
    "settings": {
        "primary_color": "#3b82f6",
        "secondary_color": "#6b7280",
        "text_color": "#1f2937",
        "font_family": "system-ui, sans-serif"
    },
    "assets": {
        "css": ["css/style.css"],
        "js": []
    },
    "views": {
        "layouts": ["layouts/app"],
        "pages": ["pages/home"]
    }
}
```

### css/style.css

```css
:root {
    --primary-color: #3b82f6;
    --secondary-color: #6b7280;
    --text-color: #1f2937;
}

body {
    font-family: system-ui, sans-serif;
    margin: 0;
    line-height: 1.6;
}

.container {
    max-width: 800px;
    margin: 0 auto;
    padding: 0 1.5rem;
}

.hero {
    padding: 4rem 0;
    text-align: center;
}

.btn {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    background: var(--primary-color);
    color: white;
    border-radius: 0.25rem;
    text-decoration: none;
}
```

### views/layouts/app.blade.php

```blade
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('themes._shared.seo')
    <link rel="stylesheet" href="{{ asset('themes/quickstart/css/style.css') }}">
</head>
<body>
    <div class="container" style="padding: 2rem 0;">
        <h1>{{ $settings->site_title ?? 'Quick Start' }}</h1>
        @yield('content')
    </div>
</body>
</html>
```

### views/pages/home.blade.php

```blade
@extends('themes.quickstart.views.layouts.app')

@section('content')
<div class="hero">
    <h2>Welcome to Quick Start Theme</h2>
    <p>This is a minimal theme to help you get started with Rosetta CMS development.</p>
    <a href="{{ route('admin.login') }}" class="btn">Go to Admin</a>
</div>
@endsection
```

---

## Additional Resources

### Official Documentation

- [Rosetta CMS Documentation](https://docs.rosetta-cms.com)
- [Laravel Blade Templates](https://laravel.com/docs/blade)
- [Laravel Facades](https://laravel.com/docs/facades)

### Community

- [Rosetta CMS GitHub](https://github.com/RosettaDev/RosettaCMS)
- [Issue Tracker](https://github.com/RosettaDev/RosettaCMS/issues)
- [Discussions](https://github.com/RosettaDev/RosettaCMS/discussions)

### Theme Examples

- `themes/rosetta/` - Default theme (most comprehensive)
- `themes/modern/` - Modern minimal theme
- `themes/elegant/` - Elegant business theme

---

## FAQ

### Q: How do I update theme settings after activation?

**A:** Edit `config/themes/{theme-slug}.php` or use the theme customizer in the admin panel.

### Q: Can I use external fonts?

**A:** Yes, add `<link>` tags in your layout template or use `@stack('styles')`.

### Q: How do I add custom post types?

**A:** Define them in `config/post-types.php` and create corresponding view templates.

### Q: Is there a theme debugger?

**A:** Use `dd($variable)` or `{{ dd($variable) }}` in Blade templates for debugging.

### Q: How do I handle RTL languages?

**A:** Create a separate stylesheet and use `dir="rtl"` in your HTML tag.

---

## Contributing

If you'd like to contribute to Rosetta CMS theme development:

1. Fork the repository
2. Create a feature branch
3. Test your changes thoroughly
4. Submit a pull request with documentation

Thank you for developing with Rosetta CMS! 🚀
