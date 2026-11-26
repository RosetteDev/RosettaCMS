# Rosetta CMS Theme Quick Reference

A quick reference guide for common theme development tasks.

## Theme Structure

```
themes/
└── {theme-slug}/
    ├── theme.json              # Required
    ├── screenshot.png          # Recommended (760x500px)
    ├── css/
    │   └── style.css           # Required
    ├── js/
    │   └── main.js             # Optional
    └── views/
        ├── layouts/app.blade.php      # Required
        ├── pages/home.blade.php       # Required
        ├── pages/about.blade.php      # Optional
        ├── pages/contact.blade.php    # Optional
        └── partials/                # Optional
            ├── header.blade.php
            ├── footer.blade.php
            ├── navigation.blade.php
            └── sidebar.blade.php
```

## Essential Files

### theme.json

```json
{
    "name": "Theme Name",
    "slug": "theme-slug",
    "version": "1.0.0",
    "description": "Theme description",
    "author": "Author Name",
    "author_url": "https://example.com",
    "screenshot": "screenshot.png",
    "supports": {
        "features": ["menu", "widgets", "customizer"],
        "layouts": ["one-column"],
        "post_types": ["post", "page"]
    },
    "settings": {
        "primary_color": "#3b82f6"
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

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1.5rem;
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
    <link rel="stylesheet" href="{{ asset('themes/theme-slug/css/style.css') }}">
    @stack('styles')
    <style>
        :root {
            --primary-color: {{ $theme_options['primary_color'] ?? '#3b82f6' }};
        }
    </style>
</head>
<body>
    @yield('content')
    @stack('scripts')
</body>
</html>
```

### views/pages/home.blade.php

```blade
@extends('themes.theme-slug.views.layouts.app')

@section('content')
<h1>{{ $site->name ?? 'Home' }}</h1>
@endsection
```

## Common Blade Components

### SEO
```blade
@include('themes._shared.seo')
```

### Navigation
```blade
@include('themes._shared.nav', ['menu' => $headerMenu ?? null])
```

### Footer
```blade
@include('themes._shared.footer')
```

### Breadcrumbs
```blade
@include('themes._shared.breadcrumbs')
```

## Available Variables

| Variable | Description |
|----------|-------------|
| `$site` | Site model |
| `$settings` | Site settings |
| `$theme_options` | Theme settings (CSS variables) |
| `$posts` | Posts collection |
| `$post` | Single post |
| `$headerMenu` | Header menu |
| `$footerMenu` | Footer menu |
| `$categories` | Categories |
| `$tags` | Tags |
| `$recentPosts` | Recent posts |

## CSS Variables

Theme settings from `theme.json` are exposed as CSS variables:

```css
:root {
    --primary-color: {{ $theme_options['primary_color'] ?? '#3b82f6' }};
    --secondary-color: {{ $theme_options['secondary_color'] ?? '#6b7280' }};
    --accent-color: {{ $theme_options['accent_color'] ?? '#10b981' }};
    --bg-color: {{ $theme_options['background_color'] ?? '#ffffff' }};
    --text-color: {{ $theme_options['text_color'] ?? '#1f2937' }};
    --font-family: {{ $theme_options['font_family'] ?? 'system-ui, sans-serif' }};
}
```

## Template Hierarchy

### Pages

| Request | Template |
|---------|----------|
| Homepage | `pages/home.blade.php` |
| About page | `pages/about.blade.php` |
| Contact page | `pages/contact.blade.php` |
| Any page | `pages/single.blade.php` |

### Posts

| Request | Template |
|---------|----------|
| Single post | `posts/single.blade.php` |
| Blog archive | `posts/archive.blade.php` |
| Category archive | `posts/category.blade.php` |
| Tag archive | `posts/tag.blade.php` |

### Other

| Request | Template |
|---------|----------|
| 404 error | `404.blade.php` |
| Search results | `search.blade.php` |
| Date archive | `archive.blade.php` |

## Common Tasks

### Add a Custom CSS File

```blade
@push('styles')
<link rel="stylesheet" href="{{ asset('themes/theme-slug/css/custom.css') }}">
@endpush
```

### Add Custom JavaScript

```blade
@push('scripts')
<script>
    console.log('Custom JS');
</script>
@endpush
```

### Link to Theme Assets

```blade
<link rel="stylesheet" href="{{ asset('themes/theme-slug/css/style.css') }}">
<script src="{{ asset('themes/theme-slug/js/main.js') }}"></script>
```

### Get Theme Options in Blade

```blade
<style>
    .element {
        color: {{ $theme_options['primary_color'] ?? '#3b82f6' }};
    }
</style>
```

## Theme Activation

1. Create theme in `themes/{theme-slug}/`
2. Run `php artisan cache:clear`
3. Go to Admin > Appearance > Themes
4. Activate your theme

## Theme Customization

### Via Theme Customizer

Admin > Appearance > Customize > {Theme Name}

### Via Config File

Edit `config/themes/{theme-slug}.php`

## Debugging

### View Available Variables

```blade
{{ dd(get_defined_vars()) }}
```

### Check Current Template

```blade
{{ request()->route() ?? 'No route' }}
```

### Get Theme Path

```blade
{{ public_path('themes/' . config('theme.active')) }}
```

## Performance Tips

1. **Minify CSS/JS** before production
2. **Use defer** for JavaScript
3. **Lazy load** images
4. **Optimize** images (WebP format)
5. **Use CSS variables** for dynamic theming

## Browser Support

Target modern browsers:
- Chrome/Edge: Current + 2 versions
- Firefox: Current + 2 versions
- Safari: Current + 2 versions
- Mobile: iOS 14+, Android 9+

---

## Quick Start Command

```bash
# Create theme directory structure
mkdir -p themes/my-theme/{css,js,views/{layouts,pages,partials}}
touch themes/my-theme/theme.json css/style.css views/layouts/app.blade.php views/pages/home.blade.php
```

## Useful Links

- [Full Theme Development Guide](./THEME_DEVELOPMENT_GUIDE.md)
- [Rosetta CMS Docs](https://docs.rosetta-cms.com)
- [Laravel Blade](https://laravel.com/docs/blade)

---

*Last Updated: November 2026*
