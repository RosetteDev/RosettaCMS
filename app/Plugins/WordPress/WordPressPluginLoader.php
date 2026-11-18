<?php

namespace RosettaCMS\Plugins\WordPress;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;

/**
 * Discovers and loads traditional WordPress plugins (single files or folders
 * containing a "Plugin Name:" header) from wp-content/plugins and plugins/.
 */
class WordPressPluginLoader
{
    /**
     * Directories scanned for WordPress plugins.
     *
     * @var array<int, string>
     */
    protected array $paths;

    /**
     * Resolved activation state.
     *
     * @var array<int, string>|null
     */
    protected ?array $activeIds = null;

    public function __construct()
    {
        $this->paths = array_values(array_filter([
            base_path('wp-content/plugins'),
            base_path('plugins'),
        ]));
    }

    /**
     * Get the scanned plugin directories.
     *
     * @return array<int, string>
     */
    public function paths(): array
    {
        return $this->paths;
    }

    /**
     * Discover plugin files that contain a WordPress plugin header.
     *
     * @return array<string, array<string, mixed>>
     */
    public function discover(): array
    {
        $plugins = [];

        foreach ($this->paths as $root) {
            if (!is_dir($root)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );

            $iterator->setMaxDepth(2);

            foreach ($iterator as $file) {
                if (!$file->isFile() || strtolower($file->getExtension()) !== 'php') {
                    continue;
                }

                $header = $this->readHeader($file->getPathname());

                if ($header === null) {
                    continue;
                }

                $realPath = $file->getPathname();
                $relative = ltrim(str_replace($root, '', $realPath), DIRECTORY_SEPARATOR);

                $plugins[md5($root . '/' . $relative)] = array_merge($header, [
                    'id' => md5($root . '/' . $relative),
                    'file' => $realPath,
                    'relative' => $relative,
                    'source' => $root,
                ]);
            }
        }

        ksort($plugins);

        return $plugins;
    }

    /**
     * Parse the WordPress plugin header from a PHP file.
     *
     * @return array<string, string>|null
     */
    protected function readHeader(string $file): ?array
    {
        $data = @file_get_contents($file, false, null, 0, 8192);

        if ($data === false || !preg_match('/^[ \t\/*#@]*Plugin Name:\s*(.+)$/mi', $data, $name)) {
            return null;
        }

        $field = function (string $label) use ($data): ?string {
            return preg_match('/^[ \t\/*#@]*' . preg_quote($label, '/') . ':\s*(.+)$/mi', $data, $match)
                ? trim($match[1])
                : null;
        };

        return [
            'name' => trim($name[1]),
            'description' => $field('Description') ?? '',
            'version' => $field('Version') ?? '1.0.0',
            'author' => $field('Author') ?? '',
            'author_uri' => $field('Author URI') ?? '',
            'plugin_uri' => $field('Plugin URI') ?? '',
            'requires' => $field('Requires at least') ?? '',
            'requires_php' => $field('Requires PHP') ?? '',
        ];
    }

    /**
     * IDs of plugins activated by the administrator.
     *
     * @return array<int, string>
     */
    public function activeIds(): array
    {
        if ($this->activeIds !== null) {
            return $this->activeIds;
        }

        $path = $this->stateFile();

        if (!is_file($path)) {
            return $this->activeIds = [];
        }

        $data = json_decode((string) @file_get_contents($path), true);

        return $this->activeIds = is_array($data['active'] ?? null) ? array_values($data['active']) : [];
    }

    public function isActive(string $id): bool
    {
        return in_array($id, $this->activeIds(), true);
    }

    public function activate(string $id): bool
    {
        $plugins = $this->discover();

        if (!isset($plugins[$id])) {
            return false;
        }

        $ids = $this->activeIds();

        if (!in_array($id, $ids, true)) {
            $ids[] = $id;
        }

        return $this->writeState($ids);
    }

    public function deactivate(string $id): bool
    {
        $ids = array_values(array_filter($this->activeIds(), fn ($active) => $active !== $id));

        return $this->writeState($ids);
    }

    /**
     * Load every active plugin and fire the standard lifecycle hooks.
     */
    public function loadActive(): void
    {
        if (!function_exists('add_action')) {
            return;
        }

        $plugins = $this->discover();

        foreach ($plugins as $id => $plugin) {
            if (!$this->isActive($id)) {
                continue;
            }

            try {
                require_once $plugin['file'];
            } catch (Throwable $e) {
                logger()->error('WordPress plugin failed to load: ' . $plugin['name'], [
                    'file' => $plugin['file'],
                    'exception' => $e,
                ]);
            }
        }

        do_action('plugins_loaded');
        do_action('init');
    }

    /**
     * Persist the active plugin IDs.
     *
     * @param array<int, string> $ids
     */
    protected function writeState(array $ids): bool
    {
        $this->activeIds = array_values(array_unique($ids));

        $path = $this->stateFile();

        if (!is_dir(dirname($path)) && !@mkdir(dirname($path), 0755, true) && !is_dir(dirname($path))) {
            return false;
        }

        return @file_put_contents(
            $path,
            json_encode(['active' => $this->activeIds], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        ) !== false;
    }

    protected function stateFile(): string
    {
        return storage_path('app/wordpress-plugins.json');
    }
}
