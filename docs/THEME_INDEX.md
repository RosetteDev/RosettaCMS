# Rosetta CMS Theme Documentation

Welcome to the Rosetta CMS Theme Documentation! This section covers everything you need to know about creating and customizing themes for Rosetta CMS.

## Documentation Guide

### 📘 Getting Started

**[Theme Quick Reference](./THEME_QUICK_REFERENCE.md)**  
*For developers who want to start coding immediately*

- Minimal theme structure
- Essential files explained
- Common components
- Quick examples
- Debugging tips

**Read this first if you want to:**
- Get a theme running in 10 minutes
- Find common patterns quickly
- Reference syntax without reading everything

---

### 📗 Comprehensive Guide

**[Theme Development Guide](./THEME_DEVELOPMENT_GUIDE.md)**  
*For developers who want deep understanding*

- Complete theme structure explanation
- All available view templates
- Theme options and customizer
- CSS and JavaScript organization
- Best practices and standards
- Accessibility guidelines
- Performance optimization
- Publishing your theme

**Read this if you want to:**
- Understand how themes work in depth
- Create production-ready themes
- Learn about theme customization
- Follow WordPress-style theme development
- Publish your theme publicly

---

## Theme Documentation Structure

```
docs/
├── THEME_INDEX.md              ← This file
├── THEME_QUICK_REFERENCE.md    ← Quick examples and syntax
├── THEME_DEVELOPMENT_GUIDE.md  ← Comprehensive guide
└── THEME_EXAMPLES/             ← Example themes (coming soon)
```

---

## What is a Theme?

A theme in Rosetta CMS controls the presentation layer of your website. It determines how content is displayed to visitors.

### Key Features

- **Blade Templates**: Use Laravel's powerful templating engine
- **Theme Customizer**: Real-time preview of theme changes
- **CSS Variables**: Dynamic theming with CSS custom properties
- **Responsive**: Mobile-first design approach
- **SEO Ready**: Built-in SEO and Open Graph support
- **Widget Support**: Sidebar and footer widget areas

---

## Quick Start Example

### Create a Theme in 5 Minutes

```bash
# 1. Create theme directory
mkdir -p themes/my-theme/{css,js,views/{layouts,pages}}

# 2. Create theme.json
echo '{"name":"My Theme","slug":"my-theme","version":"1.0.0","description":"My first theme","author":"Me","supports":{"features":["menu","widgets"],"layouts":["one-column"],"post_types":["post","page"]},"settings":{"primary_color":"#3b82f6"},"assets":{"css":["css/style.css"],"js":[]},"views":{"layouts":["layouts/app"],"pages":["pages/home"]}}' > themes/my-theme/theme.json

# 3. Create stylesheet
cat > themes/my-theme/css/style.css << 'EOF'
:root { --primary-color: #3b82f6; }
.container { max-width: 800px; margin: 0 auto; }
EOF

# 4. Create layout
cat > themes/my-theme/views/layouts/app.blade.php << 'EOF'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('themes._shared.seo')
    <link rel="stylesheet" href="{{ asset('themes/my-theme/css/style.css') }}">
</head>
<body>
    <div class="container">@yield('content')</div>
</body>
</html>
EOF

# 5. Create home page
cat > themes/my-theme/views/pages/home.blade.php << 'EOF'
@extends('themes.my-theme.views.layouts.app')
@section('content')
<h1>Welcome to My Theme!</h1>
@endsection
EOF

# 6. Activate theme
php artisan cache:clear
```

Then go to **Admin > Appearance > Themes** and activate "My Theme".

---

## Learning Path

### For Beginners

1. Read **Theme Quick Reference**
2. Create a simple theme using the quick start example
3. Customize colors in `theme.json`
4. Add basic styling in `css/style.css`

### For Intermediate Developers

1. Read **Theme Development Guide** (Sections 1-5)
2. Understand the template hierarchy
3. Create a complete theme with all required files
4. Learn about theme options and customizer

### For Advanced Developers

1. Read **Theme Development Guide** (Sections 6-10)
2. Learn about accessibility and performance
3. Create responsive, mobile-first themes
4. Contribute to the theme ecosystem

---

## Essential Topics

### Core Concepts

- [Theme Structure](./THEME_DEVELOPMENT_GUIDE.md#theme-structure)
- [Theme Configuration](./THEME_DEVELOPMENT_GUIDE.md#theme-configuration-themefile)
- [View Templates](./THEME_DEVELOPMENT_GUIDE.md#theme-views)
- [Theme Options](./THEME_DEVELOPMENT_GUIDE.md#working-with-theme-options)

### Advanced Features

- [Shared Components](./THEME_DEVELOPMENT_GUIDE.md#available-shared-components)
- [CSS & JavaScript](./THEME_DEVELOPMENT_GUIDE.md#css--javascript-assets)
- [Responsive Design](./THEME_DEVELOPMENT_GUIDE.md#theme-development-best-practices)
- [Accessibility](./THEME_DEVELOPMENT_GUIDE.md#theme-development-best-practices)

### Deployment

- [Best Practices](./THEME_DEVELOPMENT_GUIDE.md#theme-development-best-practices)
- [Creating Screenshots](./THEME_DEVELOPMENT_GUIDE.md#creating-theme-screenshots)
- [Publishing](./THEME_DEVELOPMENT_GUIDE.md#publishing-your-theme)

---

## Example Themes

### Rosetta Default (`themes/rosetta/`)

The default theme with comprehensive features:
- Responsive design
- Multiple widget areas
- SEO optimized
- Fast performance

### Modern Minimal (`themes/modern/`)

A clean, minimal theme:
- Lightweight CSS
- Fast loading
- Modern aesthetics

### Elegant Pro (`themes/elegant/`)

A professional business theme:
- Sophisticated design
- Multiple layouts
- Business-ready features

---

## Common Questions

### Q: Where do I put my custom CSS?

**A:** Add it to `css/style.css` or use `@push('styles')` in your Blade templates.

### Q: How do I add a navigation menu?

**A:** Define it in `theme.json` under `supports.features` with `["menu"]`, then use `@include('themes._shared.nav')`.

### Q: Can I use external fonts?

**A:** Yes, add `<link>` tags in your layout template or use `@push('styles')`.

### Q: How do I make my theme responsive?

**A:** Use CSS media queries and flexible units (rem, %, em) in your stylesheet.

### Q: What's the difference between theme options and theme settings?

**A:** They're the same thing. Settings are configured in `theme.json` and exposed as options in the customizer.

---

## Resources

### Official

- [Rosetta CMS Website](https://rosetta-cms.com)
- [Rosetta CMS GitHub](https://github.com/RosettaDev/RosettaCMS)
- [Issue Tracker](https://github.com/RosettaDev/RosettaCMS/issues)

### Learning

- [Laravel Blade Documentation](https://laravel.com/docs/blade)
- [Laravel Facades](https://laravel.com/docs/facades)
- [CSS Variables](https://developer.mozilla.org/en-US/docs/Web/CSS/Using_CSS_custom_properties)

### Tools

- [W3C CSS Validator](https://jigsaw.w3.org/css-validator/)
- [Lighthouse](https://developer.chrome.com/docs/lighthouse/overview/)
- [Mobile-Friendly Test](https://search.google.com/test/mobile-friendly)

---

## Contributing

Want to contribute to theme development?

1. Read the [Full Guide](./THEME_DEVELOPMENT_GUIDE.md)
2. Check existing themes for patterns
3. Test on multiple browsers
4. Follow the style guide

---

## Changelog

### November 2026

- Added comprehensive Theme Development Guide
- Created Quick Reference for rapid development
- Documented all theme features
- Added best practices and examples

---

## Support

- **Documentation Issues**: [GitHub Issues](https://github.com/RosettaDev/RosettaCMS/issues)
- **Theme Development help**: [GitHub Discussions](https://github.com/RosettaDev/RosettaCMS/discussions)
- **Feature Requests**: [GitHub Issues](https://github.com/RosettaDev/RosettaCMS/issues)

---

Happy Theming! 🎨🚀
