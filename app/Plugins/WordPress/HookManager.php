<?php

namespace RosettaCMS\Plugins\WordPress;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;

class HookManager
{
    /**
     * Storage for action hooks.
     *
     * @var array
     */
    protected $actions = [];

    /**
     * Storage for filter hooks.
     *
     * @var array
     */
    protected $filters = [];

    /**
     * Track hooks being processed to prevent infinite recursion.
     *
     * @var array
     */
    protected static $processing = [];

    /**
     * Register an action hook.
     *
     * @param string $hook
     * @param callable $callback
     * @param int $priority
     * @param int $acceptedArgs
     */
    public function addAction($hook, $callback, $priority = 10, $acceptedArgs = 1)
    {
        $this->actions[$hook][] = compact('callback', 'priority', 'acceptedArgs');
        ksort($this->actions[$hook]);
    }

    /**
     * Register a filter hook.
     *
     * @param string $hook
     * @param callable $callback
     * @param int $priority
     * @param int $acceptedArgs
     */
    public function addFilter($hook, $callback, $priority = 10, $acceptedArgs = 1)
    {
        $this->filters[$hook][] = compact('callback', 'priority', 'acceptedArgs');
        ksort($this->filters[$hook]);
    }

    /**
     * Call an action hook.
     *
     * @param string $hook
     * @param mixed ...$args
     */
    public function doAction($hook, ...$args)
    {
        if (empty($this->actions[$hook])) {
            return;
        }

        // Prevent infinite recursion by tracking hooks being processed
        if (isset(static::$processing[$hook])) {
            return;
        }
        
        static::$processing[$hook] = true;
        
        try {
            foreach ($this->actions[$hook] as $action) {
                $callback = $action['callback'];
                $callback(...array_slice($args, 0, $action['acceptedArgs']));
            }
        } finally {
            unset(static::$processing[$hook]);
        }
    }

    /**
     * Apply a filter hook.
     *
     * @param string $hook
     * @param mixed $value
     * @param mixed ...$args
     *
     * @return mixed
     */
    public function applyFilters($hook, $value, ...$args)
    {
        if (empty($this->filters[$hook])) {
            return $value;
        }

        // Prevent infinite recursion for filters that might call doAction
        if (isset(static::$processing[$hook])) {
            return $value;
        }

        static::$processing[$hook] = true;

        try {
            foreach ($this->filters[$hook] as $filter) {
                $callback = $filter['callback'];
                $value = $callback($value, ...array_slice($args, 0, $filter['acceptedArgs']));
            }
        } finally {
            unset(static::$processing[$hook]);
        }

        return $value;
    }

    /**
     * Remove an action hook.
     *
     * @param string $hook
     * @param callable $callback
     * @param int $priority
     */
    public function removeAction($hook, $callback, $priority = 10)
    {
        if (isset($this->actions[$hook])) {
            $this->actions[$hook] = array_filter($this->actions[$hook], function ($action) use ($callback, $priority) {
                return $action['callback'] !== $callback || $action['priority'] !== $priority;
            });
        }
    }

    /**
     * Remove a filter hook.
     *
     * @param string $hook
     * @param callable $callback
     * @param int $priority
     */
    public function removeFilter($hook, $callback, $priority = 10)
    {
        if (isset($this->filters[$hook])) {
            $this->filters[$hook] = array_filter($this->filters[$hook], function ($filter) use ($callback, $priority) {
                return $filter['callback'] !== $callback || $filter['priority'] !== $priority;
            });
        }
    }

    /**
     * Check if a hook exists.
     *
     * @param string $hook
     * @param callable|string|false $callback Optional callback to check for
     *
     * @return bool
     */
    public function hasHook($hook, $callback = false)
    {
        if ($callback === false) {
            return isset($this->actions[$hook]) || isset($this->filters[$hook]);
        }
        
        if (isset($this->actions[$hook])) {
            foreach ($this->actions[$hook] as $action) {
                if ($action['callback'] === $callback) {
                    return true;
                }
            }
        }
        
        if (isset($this->filters[$hook])) {
            foreach ($this->filters[$hook] as $filter) {
                if ($filter['callback'] === $callback) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Check if an action exists.
     *
     * @param string $hook
     * @param callable|string|false $callback Optional callback to check for
     *
     * @return bool
     */
    public function hasAction($hook, $callback = false)
    {
        if ($callback === false) {
            return isset($this->actions[$hook]);
        }
        
        if (isset($this->actions[$hook])) {
            foreach ($this->actions[$hook] as $action) {
                if ($action['callback'] === $callback) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Check if a filter exists.
     *
     * @param string $hook
     * @param callable|string|false $callback Optional callback to check for
     *
     * @return bool
     */
    public function hasFilter($hook, $callback = false)
    {
        if ($callback === false) {
            return isset($this->filters[$hook]);
        }
        
        if (isset($this->filters[$hook])) {
            foreach ($this->filters[$hook] as $filter) {
                if ($filter['callback'] === $callback) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Get all hooks.
     *
     * @return array
     */
    public function getHooks()
    {
        return array_unique(array_merge(array_keys($this->actions), array_keys($this->filters)));
    }

    /**
     * Register WordPress-style hooks for common events.
     */
    public function registerWordPressHooks()
    {
        // Content hooks - the_content is a filter, not an action
        $this->addFilter('the_content', [$this, 'contentHooks'], 10);
        $this->addFilter('the_content', 'wpautop', 10);
        $this->addFilter('the_content', [$this, 'galleryShortcode'], 10);
        $this->addFilter('the_content', [$this, 'embedShortcode'], 10);

        // Template hooks
        $this->addAction('wp_head', [$this, 'wpHead'], 10);
        $this->addAction('wp_footer', [$this, 'wpFooter'], 10);
        $this->addAction('wp_enqueue_scripts', [$this, 'enqueueScripts'], 10);

        // Post hooks
        $this->addAction('save_post', [$this, 'savePost'], 10, 2);
        $this->addFilter('the_title', [$this, 'theTitle'], 10, 2);

        // Comment hooks
        $this->addAction('comment_post', [$this, 'commentPost'], 10, 3);
        $this->addFilter('get_comments_number', [$this, 'commentsNumber'], 10, 2);

        // Site hooks
        $this->addAction('init', [$this, 'init'], 10);
        $this->addAction('wp_loaded', [$this, 'wpLoaded'], 10);
        $this->addAction('shutdown', [$this, 'shutdown'], 0);
    }

    /**
     * Content hooks.
     */
    public function contentHooks($content)
    {
        // Just apply filters without triggering nested doAction calls
        $content = $this->applyFilters('content_render', $content);

        return $content;
    }

    /**
     * Gallery shortcode handler.
     */
    public function galleryShortcode($content)
    {
        return preg_replace_callback('/\[gallery.*?\]/', function ($matches) {
            return $this->renderGallery($matches[0]);
        }, $content);
    }

    /**
     * Render gallery.
     */
    protected function renderGallery($atts)
    {
        return '<div class="wp-block-gallery">[Gallery would render here]</div>';
    }

    /**
     * Embed shortcode handler.
     */
    public function embedShortcode($content)
    {
        return preg_replace_callback('/\[embed.*?\]([^[]+)\[\/embed\]/', function ($matches) {
            return $this->renderEmbed($matches[1]);
        }, $content);
    }

    /**
     * Render embed.
     */
    protected function renderEmbed($url)
    {
        return '<div class="wp-block-embed">[Embed would render here]</div>';
    }

    /**
     * wp_head hook.
     */
    public function wpHead()
    {
        $this->doAction('wp_head');
    }

    /**
     * wp_footer hook.
     */
    public function wpFooter()
    {
        $this->doAction('wp_footer');
    }

    /**
     * Enqueue scripts.
     */
    public function enqueueScripts()
    {
        $this->doAction('wp_enqueue_scripts');
    }

    /**
     * save_post hook.
     */
    public function savePost($postId, $post)
    {
        $this->doAction('save_post', $postId, $post);
        $this->doAction('wp_insert_post', $postId, $post);
    }

    /**
     * the_title filter.
     */
    public function theTitle($title, $postId)
    {
        return $this->applyFilters('the_title', $title, $postId);
    }

    /**
     * comment_post hook.
     */
    public function commentPost($commentId, $commentStatus, $commentData)
    {
        $this->doAction('comment_post', $commentId, $commentData, $commentStatus);
    }

    /**
     * comments_number filter.
     */
    public function commentsNumber($count, $postId)
    {
        return $this->applyFilters('get_comments_number', $count, $postId);
    }

    /**
     * init hook.
     */
    public function init()
    {
        $this->doAction('init');
    }

    /**
     * wp_loaded hook.
     */
    public function wpLoaded()
    {
        $this->doAction('wp_loaded');
    }

    /**
     * shutdown hook.
     */
    public function shutdown()
    {
        $this->doAction('shutdown');
    }
}
