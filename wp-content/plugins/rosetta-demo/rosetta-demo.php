<?php
/**
 * Plugin Name: Rosetta Demo Widget
 * Plugin URI: https://github.com/RosetteDev/RosettaCMS
 * Description: Example WordPress-style plugin that demonstrates hooks, filters, escaping helpers and shortcodes inside Rosetta CMS.
 * Version: 1.0.0
 * Author: Rosetta CMS Team
 * Author URI: https://github.com/RosetteDev/RosettaCMS
 * Requires at least: 1.0
 * Requires PHP: 8.1
 */

// Add a small marker to the document head.
add_action('wp_head', function () {
    echo '<meta name="rosetta-demo-plugin" content="active">' . "\n";
});

// Append a note to rendered post content.
add_filter('the_content', function ($content) {
    return $content . '<p class="rosetta-demo-note" style="font-size:.9rem;color:#6b7280;">Rendered by a WordPress-style plugin.</p>';
});

// Register a shortcode that can be used in any post or page.
add_shortcode('rosetta_badge', function ($attributes) {
    $label = isset($attributes['label']) ? $attributes['label'] : 'Rosetta';

    return '<span class="rosetta-badge" style="display:inline-block;padding:.2rem .6rem;border-radius:999px;background:#3b82f6;color:#fff;font-size:.85rem;">'
        . esc_html($label)
        . '</span>';
});
