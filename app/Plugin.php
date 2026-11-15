<?php

namespace RosettaCMS;

use Illuminate\Foundation\Application;

abstract class Plugin
{
    /**
     * The Laravel application instance.
     *
     * @var Application
     */
    protected $app;

    /**
     * Plugin name.
     *
     * @var string
     */
    protected $name;

    /**
     * Plugin description.
     *
     * @var string
     */
    protected $description;

    /**
     * Plugin version.
     *
     * @var string
     */
    protected $version = '1.0.0';

    /**
     * Plugin author.
     *
     * @var string
     */
    protected $author;

    /**
     * Plugin homepage.
     *
     * @var string
     */
    protected $homepage;

    /**
     * Plugin is active.
     *
     * @var bool
     */
    protected $isActive = false;

    /**
     * Create a new plugin instance.
     */
    public function __construct()
    {
        $this->app = app();
        $this->bootstrap();
    }

    /**
     * Bootstrap the plugin.
     *
     * @return void
     */
    abstract public function bootstrap();

    /**
     * Get the plugin name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name ?? class_basename(static::class);
    }

    /**
     * Get the plugin description.
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description ?? '';
    }

    /**
     * Get the plugin version.
     *
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Get the plugin author.
     *
     * @return string
     */
    public function getAuthor(): string
    {
        return $this->author ?? '';
    }

    /**
     * Get the plugin homepage.
     *
     * @return string
     */
    public function getHomepage(): string
    {
        return $this->homepage ?? '';
    }

    /**
     * Check if the plugin can be activated.
     *
     * @return bool
     */
    public function canActivate(): bool
    {
        return true;
    }

    /**
     * Activate the plugin.
     *
     * @return bool
     */
    public function activate(): bool
    {
        if (!$this->isActive) {
            $this->isActive = true;
            return $this->boot();
        }
        return true;
    }

    /**
     * Deactivate the plugin.
     *
     * @return bool
     */
    public function deactivate(): bool
    {
        if ($this->isActive) {
            $this->isActive = false;
            return true;
        }
        return true;
    }

    /**
     * Boot the plugin.
     *
     * @return bool
     */
    public function boot(): bool
    {
        return true;
    }

    /**
     * Check if the plugin is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * Get plugin path.
     *
     * @param string $path
     * @return string
     */
    public function path(string $path = ''): string
    {
        $reflector = new \ReflectionClass($this);
        $dir = dirname($reflector->getFileName());

        return $path ? $dir . '/' . $path : $dir;
    }

    /**
     * Get plugin URL.
     *
     * @param string $path
     * @return string
     */
    public function url(string $path = ''): string
    {
        $baseUrl = config('app.url');
        $path = ltrim($path, '/');
        
        return $path ? "$baseUrl/plugins/" . $this->getName() . "/$path" : "$baseUrl/plugins/" . $this->getName();
    }

    /**
     * Handle dynamic method calls to the plugin.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call(string $method, array $parameters)
    {
        if (method_exists($this, $method)) {
            return $this->$method(...$parameters);
        }

        return $this->app->call([$this, $method], $parameters);
    }
}
