# Rosetta CMS

![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)
![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)
![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)

A modern, open-source content management system built on Laravel with a robust visual editor, multi-site support, SEO tools, and WordPress plugin compatibility.

## Features

- 🎨 **Visual Editor**: Hybrid block-based and inline visual editor for easy content creation
- 🌍 **Multi-Site**: Manage multiple websites from a single installation
- 🔍 **SEO Tools**: Built-in SEO optimization features for better search rankings
- 🔌 **Plugin System**: Extensible plugin architecture with WordPress compatibility
- 🔒 **Secure**: Enterprise-grade security with role-based access control
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

8. **Start the development server:**
   ```bash
   php artisan serve
   ```

9. **Access the application:**
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
│       └── themes/rosetta/ # Default theme
├── routes/                  # Application routes
├── storage/                 # Generated files (logs, cache, etc.)
└── tests/                   # Automated tests
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

## Roadmap

- [ ] Advanced user management
- [ ] Custom post types
- [ ] Advanced taxonomies
- [ ] REST API
- [ ] GraphQL support
- [ ] Advanced caching
- [ ] Performance optimization
- [ ] Testing suite
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
