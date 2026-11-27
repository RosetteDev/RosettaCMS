# Rosetta CMS

![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)
![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)
![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)

A modern, open-source content management system built on Laravel with a robust visual editor, multi-site support, SEO tools, and WordPress plugin compatibility.

## Features

- ✍️ **Visual Editor**: Rich text editor with media insertion, featured images, excerpts and a live SEO preview
- 📄 **Posts & Pages**: Shared publishing workflow with drafts, scheduling, staff-only previews, categories and tags
- 🔍 **SEO Tools**: Per-post titles/descriptions/canonical URLs, Open Graph/Twitter cards, XML sitemap, RSS feed and `robots.txt`
- 🧭 **Menu Editor**: Header/footer menus built from pages, posts or custom URLs
- 🎨 **Replaceable Themes**: Install themes under `themes/`, switch the active theme, preview it without activating
- 👥 **Users & Roles**: Administrators, editors and subscribers with role-aware navigation and self-service profiles
- 🔌 **Plugin System**: Built-in modules plus drop-in WordPress-style plugins (hooks, filters, shortcodes, options)
- 🧩 **REST API**: Read-only JSON API plus WordPress-compatible `/wp-json/wp/v2` endpoints for headless apps and theme compatibility
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

### One-command installer (recommended)

Rosetta CMS ships with a guided CLI installer that handles preflight checks,
`.env` generation, migrations and seeding in one step:

```bash
git clone https://github.com/RosettaDev/RosettaCMS.git
cd rosetta-cms
composer install
php artisan install
```

The installer will:

1. **Preflight-check** the server: PHP version, required extensions
   (pdo, mbstring, zip, …), writable `storage/`, dependencies and database
   connectivity — with plain-language failure messages, never stack traces.
2. **Ask for** the site name, app URL and admin email/password (or take them
   via flags, e.g. `--site-name`, `--admin-email`, `--admin-password`, `--yes`
   for non-interactive installs).
3. **Write `.env`** (SQLite by default — zero external services required).
4. **Run migrations** and **seed** the site, settings, admin role and admin
   user.
5. **Lock itself off** (`INSTALL_ENABLED=false`, `APP_INSTALLED=true`) so the
   installer cannot be re-run over a configured site.

Useful flags:

- `--dry-run` — show the preflight report and planned steps without changing anything
- `--db-connection=mysql|pgsql|sqlsrv` — use an external database instead of SQLite
- `--force` — reinstall over an existing configured site (use with care)

You can also preview the installer without writing anything:

```bash
php artisan install --dry-run
```

### Manual installation

If you prefer to set things up by hand:

1. **Copy environment file:** `cp .env.example .env`
2. **Generate application key:** `php artisan key:generate`
3. **Configure database** in `.env` (SQLite by default; for MySQL set
   `DB_CONNECTION=mysql` plus host/port/database/username/password)
4. **Run migrations:** `php artisan migrate`
5. **Seed the site and admin user:** `php artisan db:seed`
6. **Link public storage (for media uploads):** `php artisan storage:link`
7. **Start the development server:** `php artisan serve`

### Web installer (optional)

A simplified web installer is retained for environments where shell access is
impractical. It uses the same checks and the same result as the CLI installer.
Enable it first by setting `INSTALL_ENABLED=true` in `.env`, then visit
`/install`. The installer locks itself off (`INSTALL_ENABLED=false`) once it
completes.

### Access the application

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
escaping helpers, post helpers, template conditionals and shortcodes. Post
metadata (`get_post_meta`/`update_post_meta`), `wp_mail`, nonces, transients,
attachment URLs and common template tags are also available.

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
their profile, and anyone can request a single-use password reset link from the
login screen.

## Menus

Menus are managed in **Admin → Menus**. Each menu has a location (`header` or
`footer`) and contains links to CMS pages or custom URLs. The public theme
renders the header and footer menus automatically, including one level of
submenu items.

## REST API

Published content is available as JSON without authentication:

- `GET /api` – discovery document
- `GET /api/posts` and `GET /api/posts/{slug}`
- `GET /api/pages` and `GET /api/pages/{slug}`
- `GET /api/categories`, `GET /api/tags` and `GET /api/search?q=term`

Listings support `page`, `per_page` (up to 100), `category`, `tag` and
`search` parameters. Drafts and scheduled content never appear in API
responses.

WordPress-style read-only endpoints are available for themes and plugins that
expect them: `/wp-json`, `/wp-json/wp/v2/posts`, `/wp-json/wp/v2/pages`,
`/wp-json/wp/v2/categories` and `/wp-json/wp/v2/tags`. Collection responses
include `X-WP-Total` and `X-WP-TotalPages` headers.

## Roadmap

- [ ] Custom post types
- [x] REST API
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
