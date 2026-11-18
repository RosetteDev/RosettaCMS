<?php
/**
 * WordPress Compatibility Functions
 *
 * Provides the subset of the WordPress plugin API that Rosetta CMS
 * supports. Every function is guarded so it never collides with a real
 * WordPress installation.
 */

if (!function_exists('compat')) {
    /**
     * Resolve the WordPress compatibility service.
     *
     * @return \RosettaCMS\Plugins\WordPress\WordPressCompatibility
     */
    function compat()
    {
        return app()->make('wordpress.compat');
    }
}

if (!function_exists('site_url')) {
    function site_url(string $path = '', $scheme = null)
    {
        return app()->make('url')->to($path, $scheme);
    }
}

if (!function_exists('home_url')) {
    function home_url(string $path = '', $scheme = null)
    {
        return app()->make('url')->to($path, $scheme);
    }
}

if (!function_exists('plugins_url')) {
    function plugins_url(string $path = '', string $plugin = '')
    {
        $base = 'plugins';

        if ($plugin !== '') {
            $base .= '/' . basename(dirname($plugin));
        }

        return app()->make('url')->to(trim($base . '/' . ltrim($path, '/'), '/'));
    }
}

if (!function_exists('plugin_dir_path')) {
    function plugin_dir_path(string $file)
    {
        return trailingslashit(dirname($file));
    }
}

if (!function_exists('trailingslashit')) {
    function trailingslashit(string $string)
    {
        return untrailingslashit($string) . '/';
    }
}

if (!function_exists('untrailingslashit')) {
    function untrailingslashit(string $string)
    {
        return rtrim($string, '/\\');
    }
}

if (!function_exists('wp_normalize_path')) {
    function wp_normalize_path(string $path)
    {
        return str_replace('\\', '/', $path);
    }
}

if (!function_exists('wp_head')) {
    function wp_head()
    {
        foreach (compat()->getStyles() as $style) {
            echo '<link rel="stylesheet" id="' . e($style['handle']) . '-css" href="'
                . e($style['src']) . '" media="' . e($style['media']) . '">' . "\n";
        }

        foreach (compat()->getScripts(false) as $script) {
            echo '<script id="' . e($script['handle']) . '-js" src="'
                . e($script['src']) . '"></script>' . "\n";
        }

        do_action('wp_head');
    }
}

if (!function_exists('wp_footer')) {
    function wp_footer()
    {
        foreach (compat()->getScripts(true) as $script) {
            echo '<script id="' . e($script['handle']) . '-js" src="'
                . e($script['src']) . '"></script>' . "\n";
        }

        do_action('wp_footer');
    }
}

if (!function_exists('wp_enqueue_script')) {
    function wp_enqueue_script(string $handle, string $src = '', array $deps = [], $ver = false, bool $in_footer = false)
    {
        compat()->enqueueScript($handle, $src, $deps, $ver, $in_footer);
    }
}

if (!function_exists('wp_enqueue_style')) {
    function wp_enqueue_style(string $handle, string $src = '', array $deps = [], $ver = false, string $media = 'all')
    {
        compat()->enqueueStyle($handle, $src, $deps, $ver, $media);
    }
}

if (!function_exists('get_template')) {
    function get_template()
    {
        $site = app()->bound('current.site') ? app('current.site') : null;

        return $site->theme ?? config('theme.name', 'rosetta');
    }
}

if (!function_exists('get_stylesheet')) {
    function get_stylesheet()
    {
        return get_template();
    }
}

if (!function_exists('get_template_directory')) {
    function get_template_directory()
    {
        return resource_path('views/themes/' . get_template());
    }
}

if (!function_exists('get_template_directory_uri')) {
    function get_template_directory_uri()
    {
        return asset('themes/' . get_template());
    }
}

if (!function_exists('get_stylesheet_directory')) {
    function get_stylesheet_directory()
    {
        return get_template_directory();
    }
}

if (!function_exists('get_stylesheet_directory_uri')) {
    function get_stylesheet_directory_uri()
    {
        return get_template_directory_uri();
    }
}

if (!function_exists('the_content')) {
    function the_content($more_link_text = null, $strip_teaser = false)
    {
        $post = compat()->getCurrentPost();

        $content = $post->rendered_content ?? $post->content ?? '';

        echo apply_filters('the_content', $content);
    }
}

if (!function_exists('get_the_content')) {
    function get_the_content($more_link_text = null, $strip_teaser = false)
    {
        $post = compat()->getCurrentPost();

        return apply_filters('the_content', $post->rendered_content ?? $post->content ?? '');
    }
}

if (!function_exists('the_title')) {
    function the_title($before = '', $after = '', $echo = true)
    {
        $title = $before . get_the_title() . $after;

        if ($echo) {
            echo $title;

            return '';
        }

        return $title;
    }
}

if (!function_exists('the_excerpt')) {
    function the_excerpt()
    {
        $post = compat()->getCurrentPost();
        $excerpt = $post->plain_excerpt ?? $post->excerpt ?? '';

        echo apply_filters('the_excerpt', $excerpt);
    }
}

if (!function_exists('get_the_excerpt')) {
    function get_the_excerpt($post = null)
    {
        if ($post === null) {
            $post = compat()->getCurrentPost();
        }

        $excerpt = $post->plain_excerpt ?? $post->excerpt ?? '';

        return apply_filters('the_excerpt', $excerpt);
    }
}

if (!function_exists('apply_filters')) {
    function apply_filters(string $tag, $value, ...$args)
    {
        return compat()->applyFilters($tag, $value, ...$args);
    }
}

if (!function_exists('do_action')) {
    function do_action(string $tag, ...$args)
    {
        compat()->doAction($tag, ...$args);
    }
}

if (!function_exists('add_action')) {
    function add_action(string $tag, callable $function, int $priority = 10, int $accepted_args = 1)
    {
        compat()->addAction($tag, $function, $priority, $accepted_args);
    }
}

if (!function_exists('add_filter')) {
    function add_filter(string $tag, callable $function, int $priority = 10, int $accepted_args = 1)
    {
        compat()->addFilter($tag, $function, $priority, $accepted_args);
    }
}

if (!function_exists('has_action')) {
    function has_action(string $tag, $function = false)
    {
        return false;
    }
}

if (!function_exists('has_filter')) {
    function has_filter(string $tag, $function = false)
    {
        return false;
    }
}

if (!function_exists('get_post')) {
    function get_post($post = null, string $output = 'OBJECT', string $filter = 'raw')
    {
        if ($post === null) {
            return compat()->getCurrentPost();
        }

        if (is_numeric($post)) {
            return app(\RosettaCMS\Models\Post::class)->find($post);
        }

        return $post;
    }
}

if (!function_exists('get_the_ID')) {
    function get_the_ID()
    {
        $post = compat()->getCurrentPost();

        return $post->id ?? false;
    }
}

if (!function_exists('get_the_title')) {
    function get_the_title($post = 0)
    {
        $post = $post ? get_post($post) : compat()->getCurrentPost();

        return $post->title ?? '';
    }
}

if (!function_exists('get_permalink')) {
    function get_permalink($post = 0)
    {
        $post = $post ? get_post($post) : compat()->getCurrentPost();

        if (!$post) {
            return home_url('/');
        }

        return $post->public_url;
    }
}

if (!function_exists('get_the_permalink')) {
    function get_the_permalink($post = 0)
    {
        return get_permalink($post);
    }
}

if (!function_exists('get_post_type')) {
    function get_post_type($post = null)
    {
        $post = $post ? get_post($post) : compat()->getCurrentPost();

        return $post->type ?? 'post';
    }
}

if (!function_exists('has_post_thumbnail')) {
    function has_post_thumbnail($post = null)
    {
        $post = $post ? get_post($post) : compat()->getCurrentPost();

        return !empty($post->featured_image_url);
    }
}

if (!function_exists('get_the_post_thumbnail_url')) {
    function get_the_post_thumbnail_url($post = null, $size = 'post-thumbnail')
    {
        $post = $post ? get_post($post) : compat()->getCurrentPost();

        return $post->featured_image_url ?? false;
    }
}

if (!function_exists('the_post_thumbnail')) {
    function the_post_thumbnail($size = 'post-thumbnail', $attr = [])
    {
        $post = compat()->getCurrentPost();

        if (empty($post->featured_image_url)) {
            return;
        }

        echo '<img src="' . e($post->featured_image_url) . '" alt="' . e($post->title ?? '') . '">';
    }
}

if (!function_exists('is_admin')) {
    function is_admin()
    {
        return compat()->isAdmin();
    }
}

if (!function_exists('is_single')) {
    function is_single($query = '')
    {
        return compat()->isSingle($query);
    }
}

if (!function_exists('is_home')) {
    function is_home()
    {
        return compat()->isHome();
    }
}

if (!function_exists('is_front_page')) {
    function is_front_page()
    {
        return compat()->isFrontPage();
    }
}

if (!function_exists('is_category')) {
    function is_category($query = '')
    {
        return compat()->isCategory($query);
    }
}

if (!function_exists('is_tag')) {
    function is_tag($query = '')
    {
        return compat()->isTag($query);
    }
}

if (!function_exists('is_archive')) {
    function is_archive()
    {
        return compat()->isArchive();
    }
}

if (!function_exists('is_search')) {
    function is_search()
    {
        return compat()->isSearch();
    }
}

if (!function_exists('is_404')) {
    function is_404()
    {
        return compat()->is404();
    }
}

if (!function_exists('is_user_logged_in')) {
    function is_user_logged_in()
    {
        return auth()->check();
    }
}

if (!function_exists('wp_get_current_user')) {
    function wp_get_current_user()
    {
        $user = auth()->user();

        if (!$user) {
            return null;
        }

        $object = new \stdClass();
        $object->ID = $user->id;
        $object->user_login = $user->email;
        $object->user_email = $user->email;
        $object->display_name = $user->name;
        $object->user_nicename = \Illuminate\Support\Str::slug($user->name);
        $object->roles = [$user->role ?? 'subscriber'];

        return $object;
    }
}

if (!function_exists('get_current_user_id')) {
    function get_current_user_id()
    {
        return auth()->id() ?? 0;
    }
}

if (!function_exists('get_option')) {
    function get_option(string $option, $default = false)
    {
        return compat()->getOption($option, $default);
    }
}

if (!function_exists('update_option')) {
    function update_option(string $option, $value, $autoload = null)
    {
        return compat()->updateOption($option, $value);
    }
}

if (!function_exists('delete_option')) {
    function delete_option(string $option)
    {
        return true;
    }
}

if (!function_exists('get_bloginfo')) {
    function get_bloginfo(string $show = 'name', string $filter = 'raw')
    {
        $settings = \App\Models\SiteSetting::forSite();

        return match ($show) {
            'name' => $settings->site_title ?? config('app.name'),
            'description' => $settings->site_description ?? '',
            'url', 'home' => url('/'),
            'wpurl', 'siteurl' => url('/'),
            'admin_email' => $settings->contact_email ?? '',
            'charset' => 'UTF-8',
            'language' => app()->getLocale(),
            'version' => app()->version(),
            'stylesheet_url' => asset('themes/' . get_template() . '/css/style.css'),
            'template_url', 'template_directory' => get_template_directory_uri(),
            default => $settings->site_title ?? config('app.name'),
        };
    }
}

if (!function_exists('bloginfo')) {
    function bloginfo(string $show = 'name')
    {
        echo get_bloginfo($show);
    }
}

if (!function_exists('esc_html')) {
    function esc_html($text)
    {
        return e($text);
    }
}

if (!function_exists('esc_attr')) {
    function esc_attr($text)
    {
        return e($text);
    }
}

if (!function_exists('esc_textarea')) {
    function esc_textarea($text)
    {
        return e($text);
    }
}

if (!function_exists('esc_url')) {
    function esc_url($url, $protocols = null, $context = 'display')
    {
        return e($url);
    }
}

if (!function_exists('esc_url_raw')) {
    function esc_url_raw($url, $protocols = null)
    {
        return filter_var($url, FILTER_SANITIZE_URL);
    }
}

if (!function_exists('esc_js')) {
    function esc_js($text)
    {
        return addslashes($text);
    }
}

if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($str)
    {
        return trim(strip_tags((string) $str));
    }
}

if (!function_exists('sanitize_title')) {
    function sanitize_title($title, $fallback_title = '', $context = 'save')
    {
        return \Illuminate\Support\Str::slug($title ?: $fallback_title);
    }
}

if (!function_exists('wp_kses_post')) {
    function wp_kses_post($data)
    {
        return strip_tags((string) $data, '<p><a><strong><em><ul><ol><li><blockquote><code><pre><h1><h2><h3><h4><h5><h6><img><br><hr><table><thead><tbody><tr><th><td><figure><figcaption><span><div>');
    }
}

if (!function_exists('wp_strip_all_tags')) {
    function wp_strip_all_tags($text, $remove_breaks = false)
    {
        $text = strip_tags((string) $text);

        return $remove_breaks ? preg_replace('/[\r\n\t ]+/', ' ', $text) : $text;
    }
}

if (!function_exists('wpautop')) {
    function wpautop($text, $br = true)
    {
        return '<p>' . preg_replace('/\n\s*\n/', "</p>\n<p>", trim((string) $text)) . '</p>';
    }
}

if (!function_exists('absint')) {
    function absint($maybeint)
    {
        return abs((int) $maybeint);
    }
}

if (!function_exists('wp_json_encode')) {
    function wp_json_encode($data, $options = 0, $depth = 512)
    {
        return json_encode($data, $options, $depth);
    }
}

if (!function_exists('__')) {
    function __($text, $domain = 'default')
    {
        return $text;
    }
}

if (!function_exists('_e')) {
    function _e($text, $domain = 'default')
    {
        echo $text;
    }
}

if (!function_exists('_x')) {
    function _x($text, $context, $domain = 'default')
    {
        return $text;
    }
}

if (!function_exists('esc_html__')) {
    function esc_html__($text, $domain = 'default')
    {
        return e($text);
    }
}

if (!function_exists('esc_attr__')) {
    function esc_attr__($text, $domain = 'default')
    {
        return e($text);
    }
}

if (!function_exists('esc_html_e')) {
    function esc_html_e($text, $domain = 'default')
    {
        echo e($text);
    }
}

if (!function_exists('esc_attr_e')) {
    function esc_attr_e($text, $domain = 'default')
    {
        echo e($text);
    }
}

if (!function_exists('wp_nonce_field')) {
    function wp_nonce_field($action = -1, $name = '_wpnonce', $referer = true, $display = true)
    {
        $field = '<input type="hidden" name="' . e($name) . '" value="' . e(csrf_token()) . '">';

        if ($display) {
            echo $field;

            return $field;
        }

        return $field;
    }
}

if (!function_exists('add_shortcode')) {
    function add_shortcode(string $tag, callable $callback)
    {
        compat()->addShortcode($tag, $callback);
    }
}

if (!function_exists('do_shortcode')) {
    function do_shortcode(string $content)
    {
        return compat()->doShortcode($content);
    }
}
