# Rosetta CMS Themes

This directory contains all available themes for Rosetta CMS.

## Theme Directory Structure

```
themes/
├── rosetta/          # Default theme (comprehensive, well-documented)
├── modern/           # Modern minimal theme
├── elegant/          # Elegant business theme
└── [your-theme]/     # Your custom themes should go here
```

## About These Themes

### Rosetta Default (`rosetta/`)

The default theme that comes with Rosetta CMS. It's fully-featured and serves as:
- A reference implementation
- A starting point for customization
- An example of best practices

**Features:**
- Responsive design
- Multiple widget areas
- SEO optimized
- Fast performance
- Multiple layout options

### Modern Minimal (`modern/`)

A lightweight, minimalist theme ideal for:
- Developers building custom themes
- Projects requiring fast loading times
- Content-focused websites

**Features:**
- Minimal CSS
- Clean aesthetics
- Fast loading
- Easy to extend

### Elegant Pro (`elegant/`)

A professional theme for business websites:
- Sophisticated design
- Multiple layout options
- Business-ready features

## Theme Development

### Getting Started

1. **Create a new theme directory:**
   ```bash
   mkdir themes/my-awesome-theme
   ```

2. **Create the essential files:**
   - `theme.json` - Theme configuration
   - `css/style.css` - Main stylesheet
   - `views/layouts/app.blade.php` - Main layout
   - `views/pages/home.blade.php` - Homepage

3. **Activate your theme:**
   - Go to Admin > Appearance > Themes
   - Find your theme
   - Click "Activate"

### Documentation

For comprehensive theme development guides, see:
- [Theme Index](../docs/THEME_INDEX.md) - Overview and documentation links
- [Theme Quick Reference](../docs/THEME_QUICK_REFERENCE.md) - Quick examples
- [Theme Development Guide](../docs/THEME_DEVELOPMENT_GUIDE.md) - Complete guide

### Quick Theme Structure

```
my-theme/
├── theme.json              # Required - Theme configuration
├── screenshot.png          # Recommended - Preview image (760x500px)
├── css/
│   └── style.css           # Required - Main stylesheet
├── js/
│   └── main.js             # Optional - JavaScript file
└── views/
    ├── layouts/
    │   └── app.blade.php   # Required - Main layout
    ├── pages/
    │   ├── home.blade.php  # Required - Homepage
    │   ├── about.blade.php # Optional
    │   ├── contact.blade.php # Optional
    │   └── single.blade.php # Optional
    ├── posts/
    │   ├── single.blade.php # Optional
    │   └── archive.blade.php # Optional
    ├── partials/
    │   ├── header.blade.php # Optional
    │   ├── footer.blade.php # Optional
    │   └── sidebar.blade.php # Optional
    └── 404.blade.php       # Optional
```

## Creating Your Own Theme

### Option 1: Modify an Existing Theme

Copy an existing theme and customize it:

```bash
cp -r themes/rosetta themes/my-custom-theme
```

Then edit the files to customize the design.

### Option 2: Create from Scratch

Use the [Theme Quick Reference](../docs/THEME_QUICK_REFERENCE.md) for a minimal starting point.

## Theme Configuration

### theme.json

Every theme requires a `theme.json` file with this minimum structure:

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
        "features": ["menu", "widgets"],
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

## Available Shared Components

Rosetta CMS provides shared Blade components:

### SEO
```blade
@include('themes._shared.seo')
```
Includes page title, meta description, Open Graph tags, and more.

### Navigation
```blade
@include('themes._shared.nav', ['menu' => $headerMenu ?? null])
```
Displays navigation menus with dropdown support.

### Footer
```blade
@include('themes._shared.footer')
```
Standard footer with site information and links.

## Testing Your Theme

1. **Clear cache:**
   ```bash
   php artisan cache:clear
   php artisan view:clear
   ```

2. **Activate in admin:**
   - Navigate to Admin > Appearance > Themes
   - Click "Activate" on your theme

3. **Preview:**
   - Use the Theme Customizer to preview changes
   - Test on different screen sizes

## Theme Development Best Practices

1. **Follow WordPress Theme Standards** - Rosetta CMS themes follow similar conventions
2. **Mobile-First Design** - Start with mobile styles, then enhance for desktop
3. **Use CSS Variables** - Leverage theme options for dynamic styling
4. **Accessibility** - Ensure your theme is usable by everyone
5. **Performance** - Optimize images, minify assets, use efficient selectors

## Contributing

Want to contribute a new theme?

1. Follow the theme development guide
2. Test thoroughly across browsers
3. Include comprehensive documentation
4. Submit a pull request with your theme

## Support

- **Documentation**: See [docs/THEME_INDEX.md](../docs/THEME_INDEX.md)
- **Issues**: [GitHub Issues](https://github.com/RosettaDev/RosettaCMS/issues)
- **Discussions**: [GitHub Discussions](https://github.com/RosettaDev/RosettaCMS/discussions)

## License

Themes in this directory follow the same license as Rosetta CMS.

---

**Quick Link**: [← Back to Rosetta CMS Root](../README.md)
