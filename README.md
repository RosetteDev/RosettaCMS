# Rosetta CMS

![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)
![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)
![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)

A modern, open-source content management system built on Laravel with a robust visual editor, multi-site support, SEO tools, and WordPress plugin compatibility.

## Features

- ✍️ **Visual Editor**: Rich text editor with media insertion, featured images, excerpts and a live SEO preview
- 📄 **Posts & Pages**: Shared publishing workflow with drafts, scheduling, categories and tags
- 🔍 **SEO Tools**: Per-post titles/descriptions/canonical URLs, Open Graph/Twitter cards, XML sitemap, RSS feed and `robots.txt`
- 🧭 **Menu Editor**: Header/footer menus built from pages, posts or custom URLs
- 🎨 **Replaceable Themes**: Install themes under `themes/`, switch the active theme, preview it without activating
- 👥 **Users & Roles**: Administrators, editors and subscribers with role-aware navigation and self-service profiles
- 🔌 **Plugin System**: Built-in modules plus drop-in WordPress-style plugins (hooks, filters, shortcodes, options)
- 🖼️ **Media Library**: Folders, search, alt text/captions and a reusable editor picker
- 🌍 **Multi-Site**: Manage multiple websites from a single installation
- 🔒 **Secure**: Role-based access control, CSRF protection, output escaping, content sanitization and rate limiting
- ⚡ **Fast Performance**: Optimized for speed with caching and efficient database queries

## Requirements

- PHP 8.2 or higher
- Composer
- MySQL/MariaDB or SQLite
- OpenSSL, PDO, Mbstring, Tokenizer, JSON, Ctype, XML, BCMath extensions

## Installation

### Prerequisites

1. Ensure you have PHP 8.2+ installed
2. Install Composer if you haven't already
3. Create a database for Rosetta CMS

### Steps

1. **Clone the repository:**
   ```bash
   git clone https://github.com/RosettaDev/RosettaCMS.git
   cd rosetta-cms
   ```

2. **Install dependencies:**
   ```bash
   composer install
   ```

3. **Copy environment file:**
   ```bash
   cp .env.example .env
   ```

4. **Generate application key:**
   ```bash
   php artisan key:generate
   ```

5. **Configure database in `.env`:**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=rosettacms
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. **Run migrations:**
   ```bash
   php artisan migrate
   ```

7. **Run seeders (optional):**
   ```bash
   php artisan db:seed
   ```

8. **Link public storage (for media uploads):**
   ```bash
   php artisan storage:link
   ```

9. **Start the development server:**
   ```bash
   php artisan serve
   ```

10. **Access the application:**
    - Frontend: http://localhost:8000
    - Admin: http://localhost:8000/admin

## Default Credentials

If you run the seeders, the default admin user is:

- Email: admin@rosettacms.com
- Password: password

**IMPORTANT**: Change the password immediately after first login!

## Project Structure

```
rosetta-cms/
├── app/
│   ├── Models/              # Eloquent models
│   ├── Http/
│   │   ├── Controllers/     # Application controllers
│   │   └── Middleware/      # Custom middleware
│   └── Plugins/             # Plugin system
│       ├── VisualEditor/    # Visual editor plugin
│       ├── SEO/            # SEO tools plugin
│       ├── MultiSite/      # Multi-site manager plugin
│       └── WordPress/      # WordPress compatibility layer
├── config/                  # Configuration files
├── database/
│   ├── migrations/          # Database migrations
│   └── seeders/           # Database seeders
├── resources/
│   └── views/
│       ├── themes/_shared/   # Shared SEO, nav, footer, sitemap, feed
│       └── themes/rosetta/   # Default public theme views
├── themes/                   # Packaged themes (theme.json, CSS, screenshots)
├── wp-content/plugins/       # Drop-in WordPress plugins
├── routes/                   # Application routes
├── storage/                  # Generated files (logs, cache, etc.)
└── tests/                    # Automated tests
```

## Plugins

Rosetta CMS uses a modular plugin system. Each plugin is located in `app/Plugins/` and extends the base `Plugin` class.

### Available Plugins

- **VisualEditor**: Provides the hybrid visual editor with block architecture
- **SEO**: Offers SEO tools and helper classes
- **MultiSite**: Manages multiple sites from a single installation
- **WordPress**: Provides WordPress compatibility layer

### Creating a Plugin

1. Create a new directory in `app/Plugins/`
2. Create a class that extends `RosettaCMS\Plugin`
3. Register your plugin in `config/plugins.php`
4. Implement your plugin's functionality

### WordPress Plugins

Rosetta can also run traditional WordPress plugins without modification. Drop a
plugin folder (or a single PHP file) into `wp-content/plugins` or `plugins/`
using the standard plugin header:

```php
<?php
/**
 * Plugin Name: My Plugin
 * Description: Example plugin
 * Version: 1.0.0
 */
add_action('wp_head', function () {
    echo '<meta name="my-plugin" content="active">';
});
```

Then open **Admin → Plugins** and activate it. Activation state is stored in
`storage/app/wordpress-plugins.json`. The compatibility layer provides actions
and filters, `wp_head`/`wp_footer`, script and style enqueueing, options,
escaping helpers, post helpers, template conditionals and shortcodes.

See `wp-content/plugins/rosetta-demo/rosetta-demo.php` for a working example.

## Themes

Themes live in `themes/{slug}` with a `theme.json` manifest, CSS, screenshot and
optional reference views. Public pages render from
`resources/views/themes/{slug}` with a fallback to the bundled Rosetta views, so
a theme can replace only the templates it needs. The active theme is chosen per
site in **Admin → Themes**, and its palette can be overridden in **Settings**.

Administrators can preview any installed theme without activating it: the eye
button opens `/?theme={slug}` in a new tab. Visitors keep seeing the active
theme, and a banner makes the preview state obvious.

## Users and roles

Rosetta ships with three roles:

- **Administrator** – full access, including users, sites, themes, plugins and settings.
- **Editor** – posts, pages, media, categories, tags, comments and menus.
- **Subscriber** – public site access plus a self-service profile at `/profile`.

Assign roles when creating or editing a user in **Admin → Users**. Registration
can be enabled or disabled in **Settings**, and new sign-ups receive the
subscriber role. Signed-in users can change their name, email and password from
their profile.

## Menus

Menus are managed in **Admin → Menus**. Each menu has a location (`header` or
`footer`) and contains links to CMS pages or custom URLs. The public theme
renders the header and footer menus automatically, including one level of
submenu items.

## Roadmap

- [ ] Custom post types
- [ ] REST API
- [ ] GraphQL support
- [ ] Advanced caching
- [ ] Expanded automated test coverage
- [ ] Documentation website

## Contributing

We welcome contributions from the community! Please read our [Contributing Guidelines](CONTRIBUTING.md) for more information.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the GNU GPLv3 License - see the [LICENSE](LICENSE) file for details.

## Acknowledgments

- Built with [Laravel](https://laravel.com/)
- Inspired by modern CMS requirements
- Powered by the open-source community

## Support

- GitHub Issues: [Report bugs or request features](https://github.com/RosettaDev/RosettaCMS/issues)
- GitHub Discussions: [Join the community](https://github.com/RosettaDev/RosettaCMS/discussions)
- Documentation: [Visit our documentation](https://docs.rosettacms.com)

## Demo

Visit our [live demo](https://demo.rosettacms.com) to see Rosetta CMS in action.

---

Made with ❤️ by the Rosetta CMS Community
