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

        return $site?->theme ?? config('theme.name', 'rosetta');
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
        return compat()->hasAction($tag, $function);
    }
}

if (!function_exists('has_filter')) {
    function has_filter(string $tag, $function = false)
    {
        return compat()->hasFilter($tag, $function);
    }
}

if (!function_exists('get_post')) {
    function get_post($post = null, string $output = 'OBJECT', string $filter = 'raw')
    {
        if ($post === null) {
            return compat()->getCurrentPost();
        }

        if (is_numeric($post)) {
            return \App\Models\Post::find($post);
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

if (!function_exists('the_permalink')) {
    function the_permalink($post = 0)
    {
        echo e(get_permalink($post));
    }
}

if (!function_exists('get_post_type')) {
    function get_post_type($post = null)
    {
        $post = $post ? get_post($post) : compat()->getCurrentPost();

        return $post->post_type ?? 'post';
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

if (!function_exists('is_page')) {
    function is_page($query = '')
    {
        return compat()->isPage($query);
    }
}

if (!function_exists('is_singular')) {
    function is_singular($query = '')
    {
        return compat()->isSingular();
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
        $object->roles = $user->roles()->pluck('slug')->all() ?: ['subscriber'];
        $object->caps = [];

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
            'admin_email' => $settings->admin_email ?? '',
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

if (!function_exists('shortcode_atts')) {
    /**
     * Merge shortcode attributes with their defaults, like WordPress.
     */
    function shortcode_atts(array $pairs, array $atts, string $shortcode = ''): array
    {
        $atts = (array) $atts;

        if ($pairs === []) {
            return $atts;
        }

        return array_merge($pairs, array_intersect_key($atts, $pairs));
    }
}

if (!function_exists('current_user_can')) {
    /**
     * Map common WordPress capabilities onto Rosetta roles.
     */
    function current_user_can(string $capability, ...$args): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        if (!$user->hasRole('editor')) {
            return $capability === 'read';
        }

        return in_array($capability, [
            'read',
            'edit_posts',
            'edit_published_posts',
            'publish_posts',
            'delete_posts',
            'edit_pages',
            'publish_pages',
            'delete_pages',
            'upload_files',
            'edit_files',
            'moderate_comments',
            'manage_categories',
        ], true);
    }
}

if (!function_exists('wp_create_nonce')) {
    /**
     * Rosetta uses Laravel's session CSRF token for nonces.
     */
    function wp_create_nonce($action = -1)
    {
        $token = csrf_token();

        if (!$token) {
            $token = substr(hash_hmac('sha256', (string) $action, (string) config('app.key')), 0, 32);
        }

        return $token;
    }
}

if (!function_exists('wp_verify_nonce')) {
    function wp_verify_nonce($nonce, $action = -1)
    {
        return is_string($nonce) && hash_equals(wp_create_nonce($action), $nonce) ? 1 : false;
    }
}

if (!function_exists('check_admin_referer')) {
    function check_admin_referer($action = -1, $query_arg = '_wpnonce')
    {
        if (!wp_verify_nonce(request()->input($query_arg), $action)) {
            abort(403, 'The link you followed has expired.');
        }

        return true;
    }
}

if (!function_exists('add_option')) {
    function add_option(string $option, $value = '', $deprecated = '', $autoload = 'yes'): bool
    {
        if (get_option($option, null) === null) {
            update_option($option, $value);
        }

        return true;
    }
}

if (!function_exists('get_transient')) {
    function get_transient(string $transient)
    {
        return \Illuminate\Support\Facades\Cache::get('wp_transient_' . $transient);
    }
}

if (!function_exists('set_transient')) {
    function set_transient(string $transient, $value, int $expiration = 0): bool
    {
        return $expiration > 0
            ? \Illuminate\Support\Facades\Cache::put('wp_transient_' . $transient, $value, $expiration)
            : \Illuminate\Support\Facades\Cache::forever('wp_transient_' . $transient, $value);
    }
}

if (!function_exists('delete_transient')) {
    function delete_transient(string $transient): bool
    {
        return \Illuminate\Support\Facades\Cache::forget('wp_transient_' . $transient);
    }
}

if (!function_exists('wp_redirect')) {
    function wp_redirect(string $location, int $status = 302)
    {
        return redirect()->to($location, $status);
    }
}

if (!function_exists('wp_safe_redirect')) {
    function wp_safe_redirect(string $location, int $status = 302)
    {
        return wp_redirect($location, $status);
    }
}

if (!function_exists('wp_die')) {
    function wp_die(string $message = '', string $title = '', array $args = [])
    {
        abort(500, $message !== '' ? $message : 'WordPress execution stopped.');
    }
}

if (!function_exists('wp_get_attachment_url')) {
    function wp_get_attachment_url($attachmentId)
    {
        $media = \App\Models\Media::find($attachmentId);

        return $media ? $media->url : false;
    }
}

if (!function_exists('wp_mail')) {
    /**
     * Send an email using the configured Laravel mailer.
     */
    function wp_mail($to, string $subject, string $message, $headers = '', array $attachments = []): bool
    {
        $to = is_array($to) ? implode(',', $to) : (string) $to;
        $headers = is_array($headers)
            ? $headers
            : preg_split('/\r\n|\r|\n/', (string) $headers, -1, PREG_SPLIT_NO_EMPTY);

        $parseAddress = static function (string $value): array {
            $value = trim($value);

            if (preg_match('/^(.*)<([^>]+)>$/', $value, $matches)) {
                return [trim($matches[2]), trim($matches[1], " \t\"'")];
            }

            return [$value, null];
        };

        $from = null;
        $replyTo = null;
        $cc = [];
        $bcc = [];

        foreach ($headers as $header) {
            $header = trim((string) $header);

            if (stripos($header, 'From:') === 0) {
                $from = trim(substr($header, 5));
            } elseif (stripos($header, 'Reply-To:') === 0) {
                $replyTo = trim(substr($header, 9));
            } elseif (stripos($header, 'Cc:') === 0) {
                $cc[] = trim(substr($header, 3));
            } elseif (stripos($header, 'Bcc:') === 0) {
                $bcc[] = trim(substr($header, 4));
            }
        }

        try {
            \Illuminate\Support\Facades\Mail::raw($message, function ($mail) use ($to, $subject, $from, $replyTo, $cc, $bcc, $attachments, $parseAddress) {
                $mail->to($to)->subject($subject);

                if ($from) {
                    [$address, $name] = $parseAddress($from);
                    $mail->from($address, $name ?: null);
                }

                if ($replyTo) {
                    [$address, $name] = $parseAddress($replyTo);
                    $mail->replyTo($address, $name ?: null);
                }

                if ($cc !== []) {
                    foreach ($cc as $address) {
                        $mail->cc($address);
                    }
                }

                if ($bcc !== []) {
                    foreach ($bcc as $address) {
                        $mail->bcc($address);
                    }
                }

                foreach ($attachments as $attachment) {
                    if (is_string($attachment) && is_file($attachment)) {
                        $mail->attach($attachment);
                    }
                }
            });

            return true;
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }
}

if (!function_exists('get_post_meta')) {
    /**
     * Read metadata stored in a post's meta JSON column.
     */
    function get_post_meta($post_id, $key = '', $single = false)
    {
        $post = $post_id instanceof \App\Models\Post ? $post_id : get_post($post_id);

        if (!$post) {
            return $single ? '' : [];
        }

        $meta = $post->meta ?? [];

        if ($key === '') {
            return $meta;
        }

        if (!array_key_exists($key, $meta)) {
            return $single ? '' : [];
        }

        return $single ? $meta[$key] : [$meta[$key]];
    }
}

if (!function_exists('update_post_meta')) {
    /**
     * Persist a metadata value for a post.
     */
    function update_post_meta($post_id, $meta_key, $meta_value, $prev_value = ''): bool
    {
        $post = $post_id instanceof \App\Models\Post ? $post_id : get_post($post_id);

        if (!$post) {
            return false;
        }

        $meta = $post->meta ?? [];
        $meta[$meta_key] = $meta_value;
        $post->meta = $meta;

        return (bool) $post->save();
    }
}

if (!function_exists('add_post_meta')) {
    /**
     * Add a metadata value, optionally only when the key is absent.
     */
    function add_post_meta($post_id, $meta_key, $meta_value, $unique = false): bool
    {
        $post = $post_id instanceof \App\Models\Post ? $post_id : get_post($post_id);

        if (!$post) {
            return false;
        }

        $meta = $post->meta ?? [];

        if ($unique && array_key_exists($meta_key, $meta)) {
            return false;
        }

        return update_post_meta($post, $meta_key, $meta_value);
    }
}

if (!function_exists('delete_post_meta')) {
    /**
     * Remove a metadata key from a post.
     */
    function delete_post_meta($post_id, $meta_key, $meta_value = ''): bool
    {
        $post = $post_id instanceof \App\Models\Post ? $post_id : get_post($post_id);

        if (!$post) {
            return false;
        }

        $meta = $post->meta ?? [];

        if (!array_key_exists($meta_key, $meta)) {
            return false;
        }

        unset($meta[$meta_key]);
        $post->meta = $meta;

        return (bool) $post->save();
    }
}

if (!function_exists('metadata_exists')) {
    function metadata_exists(string $meta_type, $object_id, string $meta_key): bool
    {
        if ($meta_type !== 'post') {
            return false;
        }

        $post = get_post($object_id);

        return $post ? array_key_exists($meta_key, $post->meta ?? []) : false;
    }
}

if (!function_exists('get_the_date')) {
    function get_the_date(string $format = '', $post = null)
    {
        $post = $post ? get_post($post) : compat()->getCurrentPost();
        $date = $post->published_at ?? $post->created_at ?? null;

        return $date ? $date->format($format ?: 'F j, Y') : '';
    }
}

if (!function_exists('the_date')) {
    function the_date(string $format = '', string $before = '', string $after = '')
    {
        $date = get_the_date($format);

        if ($date !== '') {
            echo $before . e($date) . $after;
        }
    }
}

if (!function_exists('get_the_author')) {
    function get_the_author($post = null)
    {
        $post = $post ? get_post($post) : compat()->getCurrentPost();

        return $post->user->name ?? '';
    }
}

if (!function_exists('get_the_author_meta')) {
    function get_the_author_meta(string $field = 'display_name', $user_id = null)
    {
        $post = compat()->getCurrentPost();
        $user = $user_id
            ? \App\Models\User::find($user_id)
            : ($post->user ?? auth()->user());

        if (!$user) {
            return '';
        }

        return match ($field) {
            'ID', 'id' => $user->id,
            'user_login', 'user_email', 'email' => $user->email,
            'display_name', 'name' => $user->name,
            'user_nicename' => \Illuminate\Support\Str::slug($user->name),
            default => '',
        };
    }
}

if (!function_exists('get_the_category')) {
    function get_the_category($post = null)
    {
        $post = $post ? get_post($post) : compat()->getCurrentPost();

        if (!$post) {
            return [];
        }

        return $post->categories->map(function ($category) {
            return (object) [
                'term_id' => $category->id,
                'cat_ID' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description ?? '',
            ];
        })->all();
    }
}

if (!function_exists('get_the_tags')) {
    function get_the_tags($post = null)
    {
        $post = $post ? get_post($post) : compat()->getCurrentPost();

        if (!$post) {
            return [];
        }

        return $post->tags->map(function ($tag) {
            return (object) [
                'term_id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
                'description' => $tag->description ?? '',
            ];
        })->all();
    }
}
