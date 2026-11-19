<?php

namespace RosettaCMS\Plugins\WordPress;

class WordPressCompatibility
{
    /**
     * Registered actions.
     *
     * @var array
     */
    protected $actions = [];

    /**
     * Registered filters.
     *
     * @var array
     */
    protected $filters = [];

    /**
     * Enqueued scripts.
     *
     * @var array
     */
    protected $scripts = [];

    /**
     * Enqueued styles.
     *
     * @var array
     */
    protected $styles = [];

    /**
     * The post currently being rendered.
     *
     * @var mixed
     */
    protected $currentPost;

    /**
     * Runtime options set through update_option().
     *
     * @var array
     */
    protected $options = [];

    /**
     * Registered shortcodes.
     *
     * @var array
     */
    protected $shortcodes = [];

    /**
     * Register an action.
     *
     * @param string $tag
     * @param callable $function
     * @param int $priority
     * @param int $accepted_args
     * @return void
     */
    public function addAction(string $tag, callable $function, int $priority = 10, int $accepted_args = 1): void
    {
        $this->actions[$tag][] = [
            'function' => $function,
            'priority' => $priority,
            'accepted_args' => $accepted_args,
        ];
    }

    /**
     * Register a filter.
     *
     * @param string $tag
     * @param callable $function
     * @param int $priority
     * @param int $accepted_args
     * @return void
     */
    public function addFilter(string $tag, callable $function, int $priority = 10, int $accepted_args = 1): void
    {
        $this->filters[$tag][] = [
            'function' => $function,
            'priority' => $priority,
            'accepted_args' => $accepted_args,
        ];
    }

    /**
     * Do an action.
     *
     * @param string $tag
     * @param mixed ...$args
     * @return void
     */
    public function doAction(string $tag, ...$args): void
    {
        if (isset($this->actions[$tag])) {
            $actions = $this->actions[$tag];
            usort($actions, function ($a, $b) {
                return $a['priority'] - $b['priority'];
            });

            foreach ($actions as $action) {
                call_user_func_array($action['function'], $args);
            }
        }
    }

    /**
     * Apply a filter.
     *
     * @param string $tag
     * @param mixed $value
     * @param mixed ...$args
     * @return mixed
     */
    public function applyFilters(string $tag, $value, ...$args)
    {
        if (isset($this->filters[$tag])) {
            $filters = $this->filters[$tag];
            usort($filters, function ($a, $b) {
                return $a['priority'] - $b['priority'];
            });

            foreach ($filters as $filter) {
                $args = $filter['accepted_args'] > 1 ? [$value, ...$args] : [$value];
                $value = call_user_func_array($filter['function'], $args);
            }
        }

        return $value;
    }

    /**
     * Register default WordPress actions.
     */
    public function registerActions(): void
    {
        // WordPress initialization
        $this->addAction('init', function () {
            // Initialization logic
        });

        // Front end initialization
        $this->addAction('wp_head', function () {
            // wp_head actions
        });

        $this->addAction('wp_footer', function () {
            // wp_footer actions
        });

        // Admin actions
        $this->addAction('admin_menu', function () {
            // Admin menu registration
        });

        $this->addAction('admin_enqueue_scripts', function () {
            // Admin script enqueueing
        });

        //Save post actions
        $this->addAction('save_post', function ($post_id, $post, $update) {
            // Save post actions
        });
    }

    /**
     * Register default WordPress filters.
     */
    public function registerFilters(): void
    {
        // Content filters
        $this->addFilter('the_content', function ($content) {
            return $content;
        });

        // WordPress expands shortcodes as part of the_content (priority 11).
        $this->addFilter('the_content', function ($content) {
            return $this->doShortcode($content);
        }, 11);

        $this->addFilter('the_title', function ($title) {
            return $title;
        });

        $this->addFilter('the_excerpt', function ($excerpt) {
            return $excerpt;
        });

        // URL filters
        $this->addFilter('post_link', function ($permalink, $post) {
            return $permalink;
        });

        $this->addFilter('page_link', function ($link, $post_id) {
            return $link;
        });
    }

    /**
     * WordPress functions compatibility.
     */

    /**
     * Get site URL.
     *
     * @param string $path
     * @param string $scheme
     * @return string
     */
    public function siteUrl(string $path = '', string $scheme = 'https'): string
    {
        return site_url($path, $scheme);
    }

    /**
     * Get home URL.
     *
     * @param string $path
     * @param string $scheme
     * @return string
     */
    public function homeUrl(string $path = '', string $scheme = 'https'): string
    {
        return home_url($path, $scheme);
    }

    /**
     * Get template directory URI.
     *
     * @return string
     */
    public function templateDirectoryUri(): string
    {
        return $this->siteUrl('/wp-content/themes/' . get_template());
    }

    /**
     * Get content directory URI.
     *
     * @return string
     */
    public function contentDirectoryUri(): string
    {
        return $this->siteUrl('/wp-content');
    }

    /**
     * Get plugin directory URI.
     *
     * @param string $plugin
     * @return string
     */
    public function pluginDirectoryUri(string $plugin): string
    {
        return $this->siteUrl('/wp-content/plugins/' . dirname($plugin));
    }

    /**
     * Get plugin directory path.
     *
     * @param string $plugin
     * @return string
     */
    public function pluginDirectoryPath(string $plugin): string
    {
        return plugin_dir_path($plugin);
    }

    /**
     * Check if user is logged in.
     *
     * @return bool
     */
    public function isUserLoggedIn(): bool
    {
        return auth()->check();
    }

    /**
     * Get current user.
     *
     * @return mixed
     */
    public function currentUser()
    {
        return auth()->user();
    }

    /**
     * Enqueue a script.
     */
    public function enqueueScript(string $handle, string $src = '', array $deps = [], $ver = false, bool $inFooter = false): void
    {
        $this->scripts[$handle] = compact('handle', 'src', 'deps', 'ver', 'inFooter');
    }

    /**
     * Enqueue a style.
     */
    public function enqueueStyle(string $handle, string $src = '', array $deps = [], $ver = false, string $media = 'all'): void
    {
        $this->styles[$handle] = compact('handle', 'src', 'deps', 'ver', 'media');
    }

    /**
     * Get enqueued scripts.
     */
    public function getScripts(?bool $footer = null): array
    {
        if ($footer === null) {
            return $this->scripts;
        }

        return array_filter($this->scripts, fn ($script) => (bool) $script['inFooter'] === $footer);
    }

    /**
     * Get enqueued styles.
     */
    public function getStyles(): array
    {
        return $this->styles;
    }

    /**
     * Set the post currently being rendered.
     */
    public function setCurrentPost($post): void
    {
        $this->currentPost = $post;
    }

    /**
     * Get the post currently being rendered.
     */
    public function getCurrentPost()
    {
        return $this->currentPost;
    }

    /**
     * Register a shortcode.
     */
    public function addShortcode(string $tag, callable $callback): void
    {
        $this->shortcodes[$tag] = $callback;
    }

    /**
     * Replace registered shortcodes in a string.
     */
    public function doShortcode(string $content): string
    {
        if (empty($this->shortcodes)) {
            return $content;
        }

        return preg_replace_callback('/\[([a-zA-Z0-9_-]+)([^\]]*)\]/', function ($matches) {
            $tag = $matches[1];

            if (!isset($this->shortcodes[$tag])) {
                return $matches[0];
            }

            $attributes = [];
            $body = trim($matches[2]);

            if ($body !== '') {
                preg_match_all('/([a-zA-Z0-9_-]+)="([^"]*)"/', $body, $pairs, PREG_SET_ORDER);

                foreach ($pairs as $pair) {
                    $attributes[$pair[1]] = $pair[2];
                }
            }

            return (string) call_user_func($this->shortcodes[$tag], $attributes, '', $tag);
        }, $content);
    }

    /**
     * Get option value.
     *
     * @param string $option
     * @param mixed $default
     * @return mixed
     */
    public function getOption(string $option, $default = false)
    {
        if (array_key_exists($option, $this->options)) {
            return $this->options[$option];
        }

        try {
            $settings = \App\Models\SiteSetting::forSite();

            $map = [
                'blogname' => 'site_title',
                'blogdescription' => 'site_description',
                'admin_email' => 'admin_email',
                'posts_per_page' => 'post_per_page',
            ];

            if (isset($map[$option]) && !empty($settings->{$map[$option]})) {
                return $settings->{$map[$option]};
            }
        } catch (\Throwable $e) {
            // Database not available yet.
        }

        if ($option === 'siteurl' || $option === 'home') {
            return url('/');
        }

        return config('options.' . $option, $default);
    }

    /**
     * Update an option value for the current request.
     */
    public function updateOption(string $option, $value): bool
    {
        $this->options[$option] = $value;

        return true;
    }

    /**
     * Check if is admin area.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return request()->is('admin') || request()->is('admin/*')
            || request()->is('wp-admin') || request()->is('wp-admin/*');
    }

    /**
     * Check if is front end.
     *
     * @return bool
     */
    public function isFrontEnd(): bool
    {
        return !$this->isAdmin();
    }

    /**
     * Check if is single post.
     *
     * @param mixed $query
     * @return bool
     */
    public function isSingle($query = ''): bool
    {
        return request()->routeIs('post.show');
    }

    /**
     * Check if is home.
     *
     * @return bool
     */
    public function isHome(): bool
    {
        return request()->routeIs('home') || request()->routeIs('blog');
    }

    /**
     * Check if is front page.
     *
     * @return bool
     */
    public function isFrontPage(): bool
    {
        return request()->routeIs('home');
    }

    /**
     * Check if is category.
     *
     * @param mixed $query
     * @return bool
     */
    public function isCategory($query = ''): bool
    {
        return request()->routeIs('category.show');
    }

    /**
     * Check if is tag.
     *
     * @param mixed $query
     * @return bool
     */
    public function isTag($query = ''): bool
    {
        return request()->routeIs('tag.show');
    }

    /**
     * Check if is archive.
     *
     * @return bool
     */
    public function isArchive(): bool
    {
        return $this->isCategory() || $this->isTag();
    }

    /**
     * Check if is search.
     *
     * @return bool
     */
    public function isSearch(): bool
    {
        return request()->routeIs('search');
    }

    /**
     * Check if is 404.
     *
     * @return bool
     */
    public function is404(): bool
    {
        return false;
    }
}
