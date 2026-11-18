<?php

namespace RosettaCMS;

use Illuminate\Foundation\Application;
use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class PluginManager
{
    /**
     * The Laravel application instance.
     *
     * @var Application
     */
    protected $app;

    /**
     * Registered plugins.
     *
     * @var array
     */
    protected $plugins = [];

    /**
     * Active plugins.
     *
     * @var array
     */
    protected $activePlugins = [];

    /**
     * Plugin paths.
     *
     * @var array
     */
    protected $paths = [];

    /**
     * Create a new plugin manager instance.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        
        // Register default plugin paths
        $this->registerPath(app_path('Plugins'));
        $this->registerPath(base_path('plugins'));
    }

    /**
     * Register a plugin path.
     *
     * @param string $path
     * @return void
     */
    public function registerPath(string $path): void
    {
        if (!in_array($path, $this->paths)) {
            $this->paths[] = $path;
        }
    }

    /**
     * Scan for plugins in registered paths.
     *
     * @return array
     */
    public function scan(): array
    {
        if (!empty($this->plugins)) {
            return $this->plugins;
        }

        $plugins = [];

        foreach ($this->paths as $path) {
            if (!is_dir($path)) {
                continue;
            }

            $dir = new RecursiveDirectoryIterator($path);
            $iterator = new RecursiveIteratorIterator($dir);
            $files = new RegexIterator($iterator, '/^.+\.php$/i', RegexIterator::GET_MATCH);

            foreach ($files as $file) {
                $filePath = $file[0];

                // Plugin entry points follow the *Plugin.php naming convention.
                if (!str_ends_with(strtolower($filePath), 'plugin.php')) {
                    continue;
                }

                $className = $this->getClassNameFromFile($filePath);

                if ($className && class_exists($className)) {
                    $reflection = new \ReflectionClass($className);

                    if (!$reflection->isAbstract() && $reflection->isSubclassOf(Plugin::class)) {
                        $instance = $reflection->newInstance();
                        $plugins[$instance->getName()] = $instance;
                    }
                }
            }
        }

        return $this->plugins = $plugins;
    }

    /**
     * Resolve a fully qualified class name from a PHP file.
     */
    protected function getClassNameFromFile(string $filePath): ?string
    {
        $contents = @file_get_contents($filePath);

        if ($contents === false) {
            return null;
        }

        if (!preg_match('/namespace\s+([^;]+);/', $contents, $namespace)) {
            return null;
        }

        if (!preg_match('/(?:final\s+|abstract\s+)?class\s+([A-Za-z_][A-Za-z0-9_]*)/', $contents, $class)) {
            return null;
        }

        return trim($namespace[1]) . '\\' . $class[1];
    }

    /**
     * Get the plugins discovered in the registered paths, keyed by name.
     */
    public function discover(): array
    {
        return $this->scan();
    }

    /**
     * Activate a plugin.
     *
     * @param string $name
     * @return bool
     */
    public function activate(string $name): bool
    {
        if (isset($this->activePlugins[$name])) {
            return true;
        }

        $plugins = $this->scan();

        if (!isset($plugins[$name])) {
            return false;
        }

        $plugin = $plugins[$name];

        if (!$plugin->canActivate()) {
            return false;
        }

        $this->activePlugins[$name] = $plugin;
        $plugin->activate();

        return true;
    }

    /**
     * Deactivate a plugin.
     *
     * @param string $name
     * @return bool
     */
    public function deactivate(string $name): bool
    {
        if (!isset($this->activePlugins[$name])) {
            return false;
        }

        $plugin = $this->activePlugins[$name];
        $plugin->deactivate();
        unset($this->activePlugins[$name]);

        return true;
    }

    /**
     * Get all active plugins.
     *
     * @return array
     */
    public function getActivePlugins(): array
    {
        return $this->activePlugins;
    }

    /**
     * Get a specific active plugin.
     *
     * @param string $name
     * @return Plugin|null
     */
    public function getPlugin(string $name): ?Plugin
    {
        return $this->activePlugins[$name] ?? null;
    }

    /**
     * Enable a plugin (store as active).
     *
     * @param string $name
     * @return bool
     */
    public function enable(string $name): bool
    {
        return $this->activate($name);
    }

    /**
     * Disable a plugin (store as inactive).
     *
     * @param string $name
     * @return bool
     */
    public function disable(string $name): bool
    {
        return $this->deactivate($name);
    }
}
