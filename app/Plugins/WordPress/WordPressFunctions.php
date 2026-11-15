<?php
/**
 * WordPress Compatibility Functions
 * 
 * This file provides WordPress-style functions for Rosetta CMS
 * to ensure plugin compatibility.
 */

if (!function_exists('site_url')) {
    /**
     * Retrieve the site URL.
     *
     * @param string $path Optional. Path relative to the site URL.
     * @param string $scheme Optional. Scheme to give the site URL context.
     * @return string
     */
    function site_url(string $path = '', string $scheme = null)
    {
        return app()->make('url')->to($path, $scheme);
    }
}

if (!function_exists('home_url')) {
    /**
     * Retrieve the home URL.
     *
     * @param string $path Optional. Path relative to the home URL.
     * @param string $scheme Optional. Scheme to give the home URL context.
     * @return string
     */
    function home_url(string $path = '', string $scheme = null)
    {
        return app()->make('url')->to($path, $scheme);
    }
}

if (!function_exists('plugins_url')) {
    /**
     * Retrieve the URL to a plugin or plugins directory.
     *
     * @param string $path Optional. Path relative to the plugins URL.
     * @param string $plugin Optional. Plugin file path to get relative URL to.
     * @return string
     */
    function plugins_url(string $path = '', string $plugin = '')
    {
        return app()->make('url')->to('wp-content/plugins/' . ($plugin ? dirname($plugin) : ''));
    }
}

if (!function_exists('plugin_dir_path')) {
    /**
     * Get the filesystem directory path (with trailing slash) for the plugin __FILE__ passed in.
     *
     * @param string $file The filename of the plugin (__FILE__).
     * @return string
     */
    function plugin_dir_path(string $file)
    {
        return trailingslashit(dirname($file));
    }
}

if (!function_exists('trailingslashit')) {
    /**
     * Add trailing slash if missing.
     *
     * @param string $string String to add slash to.
     * @return string
     */
    function trailingslashit(string $string)
    {
        return untrailingslashit($string) . '/';
    }
}

if (!function_exists('untrailingslashit')) {
    /**
     * Remove trailing slash if present.
     *
     * @param string $string String to remove slash from.
     * @return string
     */
    function untrailingslashit(string $string)
    {
        return rtrim($string, '/');
    }
}

if (!function_exists('wp_head')) {
    /**
     * Trigger the wp_head action.
     */
    function wp_head()
    {
        do_action('wp_head');
    }
}

if (!function_exists('wp_footer')) {
    /**
     * Trigger the wp_footer action.
     */
    function wp_footer()
    {
        do_action('wp_footer');
    }
}

if (!function_exists('wp_enqueue_script')) {
    /**
     * Enqueue a script.
     *
     * @param string $handle Script name.
     * @param string $src Script URL.
     * @param array $deps Dependencies.
     * @param string|bool $ver Version number.
     * @param bool $in_footer Whether to load in footer.
     */
    function wp_enqueue_script(string $handle, string $src = '', array $deps = [], $ver = false, bool $in_footer = false)
    {
        app()->make('Illuminate\Contracts\View\Factory')->share('scripts', array_merge(
            app()->make('Illuminate\Contracts\View\Factory')->make('shared')->getData()['scripts'] ?? [],
            [$handle => compact('handle', 'src', 'deps', 'ver', 'in_footer')]
        ));
    }
}

if (!function_exists('wp_enqueue_style')) {
    /**
     * Enqueue a style.
     *
     * @param string $handle Style name.
     * @param string $src Style URL.
     * @param array $deps Dependencies.
     * @param string|bool $ver Version number.
     * @param string $media Media type.
     */
    function wp_enqueue_style(string $handle, string $src = '', array $deps = [], $ver = false, string $media = 'all')
    {
        app()->make('Illuminate\Contracts\View\Factory')->share('styles', array_merge(
            app()->make('Illuminate\Contracts\View\Factory')->make('shared')->getData()['styles'] ?? [],
            [$handle => compact('handle', 'src', 'deps', 'ver', 'media')]
        ));
    }
}

if (!function_exists('get_template')) {
    /**
     * Retrieve current theme name.
     *
     * @return string
     */
    function get_template()
    {
        return config('theme.name', 'default');
    }
}

if (!function_exists('get_template_directory')) {
    /**
     * Retrieve current theme directory path.
     *
     * @return string
     */
    function get_template_directory()
    {
        return resource_path('views/themes/' . get_template());
    }
}

if (!function_exists('get_template_directory_uri')) {
    /**
     * Retrieve current theme directory URI.
     *
     * @return string
     */
    function get_template_directory_uri()
    {
        return app()->make('url')->to('wp-content/themes/' . get_template());
    }
}

if (!function_exists('the_content')) {
    /**
     * Display the post content.
     *
     * @param string $more_link_text Optional. Content for when there is more text.
     * @param bool $strip_teaser Optional. Strip teaser content before the more text.
     */
    function the_content($more_link_text = null, $strip_teaser = false)
    {
        $post = app()->make('RosettaCMS\Models\Post');
        echo apply_filters('the_content', $post->content ?? '');
    }
}

if (!function_exists('the_title')) {
    /**
     * Display or return the post title.
     *
     * @param string $before Optional. Text to prepend to title.
     * @param string $after Optional. Text to append to title.
     * @param bool $echo Optional. Whether to echo or return.
     * @return string
     */
    function the_title($before = '', $after = '', $echo = true)
    {
        $post = app()->make('RosettaCMS\Models\Post');
        $title = $post->title ?? '';
        
        $title = $before . $title . $after;
        
        if ($echo) {
            echo $title;
            return '';
        }
        
        return $title;
    }
}

if (!function_exists('the_excerpt')) {
    /**
     * Display the post excerpt.
     */
    function the_excerpt()
    {
        $post = app()->make('RosettaCMS\Models\Post');
        $excerpt = apply_filters('the_excerpt', $post->content ?? '');
        
        echo $excerpt;
    }
}

if (!function_exists('apply_filters')) {
    /**
     * Call the functions added to a filter hook.
     *
     * @param string $tag The name of the filter hook.
     * @param mixed $value The value to filter.
     * @param mixed ...$args Additional parameters to pass to the callback functions.
     * @return mixed
     */
    function apply_filters(string $tag, $value, ...$args)
    {
        return app()->make('wordpress.compat')->applyFilters($tag, $value, ...$args);
    }
}

if (!function_exists('do_action')) {
    /**
     * Call the functions added to an action hook.
     *
     * @param string $tag The name of the action hook.
     * @param mixed ...$args Additional parameters to pass to the callback functions.
     */
    function do_action(string $tag, ...$args)
    {
        app()->make('wordpress.compat')->doAction($tag, ...$args);
    }
}

if (!function_exists('add_action')) {
    /**
     * Hooks a function on to a specific action.
     *
     * @param string $tag The name of the action to which the function is hooked.
     * @param callable $function The name of the function to be called.
     * @param int $priority Optional. Used to specify the order in which the functions
     *                      associated with a particular action are executed.
     * @param int $accepted_args Optional. The number of arguments the function accepts.
     */
    function add_action(string $tag, callable $function, int $priority = 10, int $accepted_args = 1)
    {
        app()->make('wordpress.compat')->addAction($tag, $function, $priority, $accepted_args);
    }
}

if (!function_exists('add_filter')) {
    /**
     * Hooks a function on to a specific filter.
     *
     * @param string $tag The name of the filter to hook the $function to.
     * @param callable $function The name of the function to be called.
     * @param int $priority Optional. Used to specify the order in which the functions
     *                      associated with a particular action are executed.
     * @param int $accepted_args Optional. The number of arguments the function accepts.
     */
    function add_filter(string $tag, callable $function, int $priority = 10, int $accepted_args = 1)
    {
        app()->make('wordpress.compat')->addFilter($tag, $function, $priority, $accepted_args);
    }
}

if (!function_exists('get_post')) {
    /**
     * Retrieve post data.
     *
     * @param int|WP_Post $post Post ID or post object.
     * @param string $output Optional. Output type.
     * @param string $filter Optional. Filter type.
     * @return mixed
     */
    function get_post($post = null, string $output = OBJECT, string $filter = 'raw')
    {
        if (is_int($post)) {
            return app()->make('RosettaCMS\Models\Post')->find($post);
        }
        
        return $post;
    }
}

if (!function_exists('get_the_ID')) {
    /**
     * Retrieve the current post ID.
     *
     * @return int|false False on error, Post ID on success.
     */
    function get_the_ID()
    {
        $post = app()->make('RosettaCMS\Models\Post');
        return $post->id ?? false;
    }
}

if (!function_exists('get_the_title')) {
    /**
     * Retrieve the post title.
     *
     * @param int|WP_Post $post Optional. Post ID or post object.
     * @return string
     */
    function get_the_title($post = 0)
    {
        $post = get_post($post);
        return $post->title ?? '';
    }
}

if (!function_exists('has_post_thumbnail')) {
    /**
     * Check if the post has a post thumbnail.
     *
     * @param int|WP_Post $post Optional. Post ID or post object.
     * @return bool
     */
    function has_post_thumbnail($post = null)
    {
        return false; // Placeholder - implement media relations later
    }
}

if (!function_exists('the_post_thumbnail')) {
    /**
     * Display the post thumbnail.
     *
     * @param string|array $size Optional. Image size.
     * @param array $attr Optional. Image attributes.
     */
    function the_post_thumbnail($size = 'post-thumbnail', $attr = [])
    {
        // Placeholder - implement media relations later
    }
}
