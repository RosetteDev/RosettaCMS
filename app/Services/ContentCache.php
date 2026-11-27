<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Invalidates full-page caches for the public site.
 *
 * The full-page cache is keyed by a global content version; bumping the
 * version makes every cached page stale immediately. The object caches
 * (posts, menus, settings) expire on their own short TTLs.
 */
class ContentCache
{
    public static function bump(): void
    {
        Cache::put('content_version', now()->timestamp, 3600);
    }
}
