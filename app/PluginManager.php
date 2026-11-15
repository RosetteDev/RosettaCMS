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
        $plugins = [];

        foreach ($this->paths as $path) {
            if (!is_dir($path)) {
                continue;
            }

            $dir = new RecursiveDirectoryIterator($path);
            $iterator = new RecursiveIteratorIterator($dir);
            $files = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

            foreach ($files as $file) {
                $filePath = $file[0];
                $className = $this->getClassNameFromFile($filePath);

                if ($className && class_exists($className)) {
                    $reflection = new \ReflectionClass($className);
                    
                    if ($reflection->isSubclassOf(Plugin::class)) {
                        $instance = $reflection->newInstance();
                        $plugins[$instance->getName()] = $instance;
                    }
                }
            }
        }

        return $plugins;
    }

    /**
     * Get class name from file path.
     *
     * @param string $filePath
     * @return string|null
     */
    protected function getClassNameFromFile(string $filePath): ?string
    {
        $relativePath = str_replace([$this->app->basePath(), '.php'], ['', ''], $filePath);
        $namespace = str_replace(['/', '\\'], ['\\', '\\'], $relativePath);
        
        // Try to autoload
        $className = 'App' . $namespace;
        if (class_exists($className)) {
            return $className;
        }

        // Try without App namespace
        $className = str_replace('App\\', '', $namespace);
        if (class_exists($className)) {
            return $className;
        }

        return null;
    }

    /**
     * Activate a plugin.
     *
     * @param string $name
     * @return bool
     */
    public function activate(string $name): bool
    {
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
