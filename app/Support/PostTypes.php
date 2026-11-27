<?php

namespace App\Support;

/**
 * Read-only access to the custom post-type registry.
 */
class PostTypes
{
    /**
     * All registered post types, keyed by slug.
     */
    public static function all(): array
    {
        return config('post_types', []);
    }

    /**
     * The slugs of the registered types.
     */
    public static function slugs(): array
    {
        return array_keys(self::all());
    }

    /**
     * Non-default types that use the generic controller (everything but the
     * "post" type, which has its own dedicated controller).
     */
    public static function generic(): array
    {
        return array_filter(self::all(), fn ($def) => empty($def['dedicated_controller']));
    }

    /**
     * Look up a single type definition.
     */
    public static function get(string $slug): ?array
    {
        $types = self::all();

        return $types[$slug] ?? null;
    }

    /**
     * Whether a slug is a registered post type.
     */
    public static function isSupported(string $slug): bool
    {
        return isset(self::all()[$slug]);
    }

    /**
     * Whether a slug uses the generic controller.
     */
    public static function isGeneric(string $slug): bool
    {
        $def = self::get($slug);

        return $def !== null && empty($def['dedicated_controller']);
    }
}
