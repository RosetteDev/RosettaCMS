<?php

namespace RosettaCMS\Plugins\WordPress;

class WordPressCompatibility
{
    /**
     * Hook manager instance.
     *
     * @var HookManager
     */
    protected $hooks;

    /**
     * Script and style storage.
     *
     * @var array
     */
    protected $scripts = [];
    protected $styles = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->hooks = app(HookManager::class);
        $this->hooks->registerWordPressHooks();

        // Listen for enqueue events
        app('events')->listen(\App\Events\ScriptEnqueued::class, [$this, 'handleScriptEnqueued']);
        app('events')->listen(\App\Events\StyleEnqueued::class, [$this, 'handleStyleEnqueued']);
        app('events')->listen(\App\Events\ScriptLocalized::class, [$this, 'handleScriptLocalized']);
    }

    /**
     * Handle script enqueued event.
     */
    public function handleScriptEnqueued($event)
    {
        $this->scripts[$event->handle] = [
            'src' => $event->src,
            'deps' => $event->deps,
            'ver' => $event->ver,
            'in_footer' => $event->in_footer,
            'handle' => $event->handle,
        ];
    }

    /**
     * Handle style enqueued event.
     */
    public function handleStyleEnqueued($event)
    {
        $this->styles[$event->handle] = [
            'src' => $event->src,
            'deps' => $event->deps,
            'ver' => $event->ver,
            'media' => $event->media,
            'handle' => $event->handle,
        ];
    }

    /**
     * Handle script localized event.
     */
    public function handleScriptLocalized($event)
    {
        // Store localized data for later use
        if (!isset($this->localized[$event->handle])) {
            $this->localized[$event->handle] = [];
        }
        $this->localized[$event->handle][$event->objectName] = $event->l10n;
    }

    /**
     * Get hook manager instance.
     *
     * @return HookManager
     */
    public function hooks()
    {
        return $this->hooks;
    }

    /**
     * WordPress wp_enqueue_script function.
     */
    public function enqueueScript($handle, $src = '', $deps = [], $ver = false, $in_footer = false)
    {
        // Register the script with Laravel's asset system
        app('events')->dispatch(new \App\Events\ScriptEnqueued($handle, $src, $deps, $ver, $in_footer));
    }

    /**
     * WordPress wp_enqueue_style function.
     */
    public function enqueueStyle($handle, $src = '', $deps = [], $ver = false, $media = 'all')
    {
        // Register the style with Laravel's asset system
        app('events')->dispatch(new \App\Events\StyleEnqueued($handle, $src, $deps, $ver, $media));
    }

    /**
     * WordPress wp_localize_script function.
     */
    public function localizeScript($handle, $objectName, $l10n)
    {
        // Localize script data
        app('events')->dispatch(new \App\Events\ScriptLocalized($handle, $objectName, $l10n));
    }

    /**
     * WordPress esc_html function.
     */
    public function escHtml($text)
    {
        return e($text);
    }

    /**
     * WordPress esc_attr function.
     */
    public function escAttr($text)
    {
        return e($text, false);
    }

    /**
     * WordPress esc_url function.
     */
    public function escUrl($url, $protocols = null, $context = 'display')
    {
        $url = strip_tags($url);
        $url = str_replace(' ', '%20', $url);
        $url = preg_replace('|[^a-z0-9-~+_.?#=&;,/:%!$*\'()@|\\[\\]\\x80-\\xff]|i', '', $url);

        if ('' === $url) {
            return $url;
        }

        if (0 !== stripos($url, 'http')) {
            $url = 'https://' . $url;
        }

        return $url;
    }

    /**
     * WordPress wp_kses_post function.
     */
    public function wp_kses_post($data)
    {
        $allowed_tags = [
            'a' => ['href' => true, 'title' => true, 'target' => true],
            'br' => [],
            'em' => [],
            'strong' => [],
            'p' => [],
            'ul' => [],
            'ol' => [],
            'li' => [],
            'h1' => [],
            'h2' => [],
            'h3' => [],
            'h4' => [],
            'h5' => [],
            'h6' => [],
            'blockquote' => [],
            'code' => [],
            'pre' => [],
            'img' => ['src' => true, 'alt' => true, 'width' => true, 'height' => true],
        ];

        return strip_tags($data, implode('', array_map(function ($tag) {
            return "<{$tag}>";
        }, array_keys($allowed_tags))));
    }

    /**
     * WordPress wpautop function.
     */
    public function wpautop($pee, $br = true)
    {
        $pee = preg_replace('/<script.*?>.*?<\/script>/is', '', $pee);
        $pee = str_replace(["\r\n", "\r"], "\n", $pee);
        $pee = preg_replace('|<br />\s*<br />|', "\n\n", $pee);
        $pee = preg_replace('|^\s+>|', '', $pee);
        $pee = preg_replace('|<\s/p>|', "</p>\n", $pee);
        $pee = preg_replace('|<\s*br\\s*/?>|i', "<br />\n", $pee);
        $pee = preg_replace('/\n\n+/', "\n\n", $pee);
        $pee = preg_replace('/\n(?=[^<])/', " <br />\n", $pee);
        $pee = preg_replace('|(<p[^>]*>)\s*|', '$1', $pee);
        $pee = preg_replace('|\s*(</p[^>]*>)|', '$1', $pee);
        $pee = preg_replace('|(<p[^>]*>)\s*</p>|', '$1', $pee);

        return $pee;
    }

    /**
     * WordPress the_content function.
     */
    public function theContent($more_link_text = null, $strip_teaser = false)
    {
        $content = get_the_content();
        $content = $this->applyFilters('the_content', $content);
        $content = str_replace(']]>', ']]&gt;', $content);

        return $content;
    }

    /**
     * WordPress the_title function.
     */
    public function theTitle($before = '', $after = '', $echo = true)
    {
        $title = get_the_title();

        if ($echo) {
            echo $before . $title . $after;
        }

        return $before . $title . $after;
    }

    /**
     * WordPress the_excerpt function.
     */
    public function the_excerpt($more_link_text = null, $strip_teaser = false)
    {
        $output = get_the_excerpt();
        $output = $this->applyFilters('the_excerpt', $output);

        return $output;
    }

    /**
     * WordPress get_the_post_thumbnail function.
     */
    public function getPostThumbnail($postId = null, $size = 'post-thumbnail', $attr = '')
    {
        $post = $postId ? get_post($postId) : null;

        if (!$post) {
            return '';
        }

        $thumbnailId = get_post_thumbnail_id($postId);
        if (!$thumbnailId) {
            return '';
        }

        $thumbnail = wp_get_attachment_image($thumbnailId, $size, false, $attr);

        return $thumbnail ?: '';
    }

    /**
     * WordPress the_post_thumbnail function.
     */
    public function thePostThumbnail($postId = null, $size = 'post-thumbnail', $attr = '')
    {
        $thumbnail = $this->getPostThumbnail($postId, $size, $attr);

        if ($thumbnail) {
            echo $thumbnail;
        }

        return $thumbnail;
    }

    /**
     * WordPress the_permalink function.
     */
    public function thePermalink()
    {
        $url = get_permalink();
        echo $url;
        return $url;
    }

    /**
     * WordPress get_permalink function.
     */
    public function getPermalink($post = null)
    {
        return get_permalink($post);
    }

    /**
     * WordPress home_url function.
     */
    public function homeUrl($path = '', $scheme = null)
    {
        $homeUrl = config('app.url');

        if ($path && is_string($path)) {
            $homeUrl = rtrim($homeUrl, '/') . '/' . ltrim($path, '/');
        }

        return $homeUrl;
    }

    /**
     * WordPress site_url function.
     */
    public function siteUrl($path = '', $scheme = null)
    {
        return $this->homeUrl($path, $scheme);
    }

    /**
     * WordPress admin_url function.
     */
    public function adminUrl($path = '', $scheme = 'admin')
    {
        return $this->homeUrl('admin/' . ltrim($path, '/'), $scheme);
    }

    /**
     * WordPress plugin_dir_url function.
     */
    public function pluginDirUrl($file)
    {
        $dir = dirname($file);
        $webDir = str_replace(base_path(), public_path(), $dir);
        return trailingslashit("{$webDir}/");
    }

    /**
     * WordPress plugin_dir_path function.
     */
    public function pluginDirPath($file)
    {
        return trailingslashit(dirname($file));
    }

    /**
     * WordPress plugin_url function.
     */
    public function pluginUrl($path, $file)
    {
        $dir = dirname($file);
        $relative = ltrim(str_replace(dirname(__DIR__), '', $dir), '/');
        return $this->homeUrl("plugins/{$relative}/{$path}");
    }

    /**
     * WordPress wp_nonce_url function.
     */
    public function wpNonceUrl($action = -1, $name = '_wpnonce')
    {
        $nonce = wp_create_nonce($action);
        return add_query_arg($name, $nonce);
    }

    /**
     * WordPress wp_create_nonce function.
     */
    public function wpCreateNonce($action = -1)
    {
        return md5(uniqid(microtime(), true));
    }

    /**
     * WordPress wp_verify_nonce function.
     */
    public function wpVerifyNonce($nonce, $action = -1)
    {
        return !empty($nonce);
    }

    /**
     * WordPress current_time function.
     */
    public function currentTime($type = 'timestamp', $gmt = 0)
    {
        return $gmt ? time() : current_time('timestamp');
    }

    /**
     * WordPress date_i18n function.
     */
    public function dateI18n($format, $timestamp = false, $gmt = false)
    {
        $timestamp = $timestamp ?: time();
        return date($format, $timestamp);
    }

    /**
     * WordPress printf function.
     */
    public function printf($format, ...$args)
    {
        return printf($format, ...$args);
    }

    /**
     * WordPress esc_html__ function.
     */
    public function escHtml__( $text, $domain = 'default' )
    {
        return e(__($text, $domain));
    }

    /**
     * WordPress esc_html_e function.
     */
    public function escHtmlE($text, $domain = 'default')
    {
        echo $this->escHtml__($text, $domain);
    }

    /**
     * WordPress __ function.
     */
    public function translate($text, $domain = 'default')
    {
        return __($text, $domain);
    }

    /**
     * WordPress _e function.
     */
    public function translateE($text, $domain = 'default')
    {
        echo __($text, $domain);
    }

    /**
     * Get enqueued styles.
     */
    public function getStyles($handle = null)
    {
        if ($handle) {
            return $this->styles[$handle] ?? [];
        }
        return array_values($this->styles);
    }

    /**
     * Get enqueued scripts.
     */
    public function getScripts($in_footer = false, $handle = null)
    {
        $filtered = [];
        foreach ($this->scripts as $script) {
            if ($script['in_footer'] === $in_footer) {
                $filtered[] = $script;
            }
        }

        if ($handle) {
            return $this->scripts[$handle] ?? [];
        }

        return array_values($filtered);
    }

    /**
     * Check if current page is a single post.
     */
    public function isSingle()
    {
        return app()->make('request')->is('posts/*') || app()->make('request')->is('category/*');
    }

    /**
     * Check if current page is a page.
     */
    public function isPage()
    {
        return app()->make('request')->is('pages/*');
    }

    /**
     * Check if current page is home.
     */
    public function isHome()
    {
        return app()->make('request')->is('/') || app()->make('request')->is('home');
    }

    /**
     * Check if user is logged in.
     */
    public function isUserLoggedIn()
    {
        return auth()->check();
    }

    /**
     * Get current user.
     */
    public function wpGetCurrentUser()
    {
        return auth()->user();
    }

    /**
     * bloginfo function.
     */
    public function bloginfo($show = '', $preview = false)
    {
        $info = [
            'url' => config('app.url'),
            'name' => config('app.name'),
            'description' => config('app.description', ''),
        ];

        if ($show && isset($info[$show])) {
            echo $info[$show];
            return $info[$show];
        }

        return $info[$show] ?? '';
    }

    /**
     * Get current post.
     */
    public function getCurrentPost()
    {
        return app()->make('request')->route('post') ?? null;
    }

    /**
     * Register a post type.
     */
    public function registerPostType($post_type, $args = [])
    {
        // Post types are registered in the database or config
        return true;
    }

    /**
     * Register a taxonomy.
     */
    public function registerTaxonomy($taxonomy, $object_type, $args = [])
    {
        // Taxonomies are registered in the database
        return true;
    }

    /**
     * Proxy to hook manager methods.
     */
    public function addAction($hook, $callback, $priority = 10, $acceptedArgs = 1)
    {
        return $this->hooks->addAction($hook, $callback, $priority, $acceptedArgs);
    }

    public function addFilter($hook, $callback, $priority = 10, $acceptedArgs = 1)
    {
        return $this->hooks->addFilter($hook, $callback, $priority, $acceptedArgs);
    }

    public function doAction($hook, ...$args)
    {
        return $this->hooks->doAction($hook, ...$args);
    }

    public function applyFilters($hook, $value, ...$args)
    {
        return $this->hooks->applyFilters($hook, $value, ...$args);
    }

    public function removeAction($hook, $callback, $priority = 10)
    {
        return $this->hooks->removeAction($hook, $callback, $priority);
    }

    public function removeFilter($hook, $callback, $priority = 10)
    {
        return $this->hooks->removeFilter($hook, $callback, $priority);
    }

    public function hasHook($hook, $callback = false)
    {
        return $this->hooks->hasHook($hook, $callback);
    }

    public function hasAction($hook, $callback = false)
    {
        return $this->hooks->hasAction($hook, $callback);
    }

    public function hasFilter($hook, $callback = false)
    {
        return $this->hooks->hasFilter($hook, $callback);
    }

    public function getHooks()
    {
        return $this->hooks->getHooks();
    }

    /**
     * Register a shortcode.
     */
    public function addShortcode($tag, $callback)
    {
        $this->addFilter('the_content', function ($content) use ($tag, $callback) {
            $pattern = '/\[' . preg_quote($tag) . '(\s+[^\]]+)?\]/';
            return preg_replace_callback($pattern, function ($matches) use ($callback) {
                $attrs = [];
                if (isset($matches[1])) {
                    $attrPattern = '/(\w+)\s*=\s*([\"\'"])([^\"\'"]*)\2/';
                    if (preg_match_all($attrPattern, $matches[1], $attrMatches, PREG_SET_ORDER)) {
                        foreach ($attrMatches as $match) {
                            $attrs[$match[1]] = $match[3];
                        }
                    }
                }
                return $callback($attrs);
            }, $content);
        });
        return true;
    }

    /**
     * Check if single post, page, or custom post type.
     */
    public function isSingular($post_types = '')
    {
        $request = app()->make('request');
        
        if (empty($post_types)) {
            // is_singular() returns true for any single post, page, or custom post type
            return $request->is('posts/*') || $request->is('pages/*');
        }
        
        // Check if post type matches
        if (is_string($post_types)) {
            if ($post_types === 'post' && $request->is('posts/*')) {
                return true;
            }
            if ($post_types === 'page' && $request->is('pages/*')) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if inside WordPress admin.
     */
    public function isAdmin()
    {
        return app()->make('request')->is('admin/*');
    }

    /**
     * Check if WordPress version.
     */
    public function isWP($version)
    {
        return true;
    }

    /**
     * Set current post for template tags.
     */
    public function setCurrentPost($post)
    {
        $this->currentPost = $post;
        return $post;
    }
}
