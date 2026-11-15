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
     * Get option value.
     *
     * @param string $option
     * @param mixed $default
     * @return mixed
     */
    public function getOption(string $option, $default = false)
    {
        return config('options.' . $option, $default);
    }

    /**
     * Check if is admin area.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return request()->is('admin/*') || request()->is('wp-admin/*');
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
        return is_single($query);
    }

    /**
     * Check if is home.
     *
     * @return bool
     */
    public function isHome(): bool
    {
        return is_home();
    }

    /**
     * Check if is front page.
     *
     * @return bool
     */
    public function isFrontPage(): bool
    {
        return is_front_page();
    }

    /**
     * Check if is category.
     *
     * @param mixed $query
     * @return bool
     */
    public function isCategory($query = ''): bool
    {
        return is_category($query);
    }

    /**
     * Check if is tag.
     *
     * @param mixed $query
     * @return bool
     */
    public function isTag($query = ''): bool
    {
        return is_tag($query);
    }

    /**
     * Check if is archive.
     *
     * @return bool
     */
    public function isArchive(): bool
    {
        return is_archive();
    }

    /**
     * Check if is search.
     *
     * @return bool
     */
    public function isSearch(): bool
    {
        return is_search();
    }

    /**
     * Check if is 404.
     *
     * @return bool
     */
    public function is404(): bool
    {
        return is_404();
    }
}
