<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;

/**
 * Cache helper trait for common caching patterns.
 */
trait CacheHelper
{
    /**
     * Cache duration for frequently changing data (30 seconds).
     */
    protected int $shortCacheDuration = 30;

    /**
     * Cache duration for moderately changing data (5 minutes).
     */
    protected int $mediumCacheDuration = 300;

    /**
     * Cache duration for rarely changing data (1 hour).
     */
    protected int $longCacheDuration = 3600;

    /**
     * Cache duration for rarely changing data (24 hours).
     */
    protected int $veryLongCacheDuration = 86400;

    /**
     * Get site settings with caching.
     */
    protected function settingsWithCache(): \App\Models\SiteSetting
    {
        $site = $this->site();
        $key = $site?->id ?? 0;

        return Cache::remember(
            "site_settings.{$key}",
            $this->mediumCacheDuration,
            fn () => \App\Models\SiteSetting::forSite($site)
        );
    }

    /**
     * Get menu with caching.
     */
    protected function menuWithCache(string $location, ?\App\Models\Site $site = null): \Illuminate\Support\Collection
    {
        $site = $site ?? $this->site();
        $siteId = $site?->id ?? 0;

        return Cache::remember(
            "menu.{$location}.{$siteId}",
            $this->longCacheDuration,
            fn () => \App\Models\Menu::forLocation($location, $site)
        );
    }

    /**
     * Get posts with caching.
     */
    protected function postsWithCache(
        int $limit = 10,
        ?\App\Models\Site $site = null,
        string $postType = 'post'
    ): \Illuminate\Support\Collection {
        $site = $site ?? $this->site();
        $siteId = $site?->id ?? 0;

        return Cache::remember(
            "posts.{$postType}.{$siteId}.limit.{$limit}",
            $this->mediumCacheDuration,
            function () use ($limit, $site, $postType) {
                return $this->scoped(\App\Models\Post::query())
                    ->published()
                    ->where('post_type', $postType)
                    ->orderByDesc('published_at')
                    ->with(['author', 'categories', 'tags'])
                    ->limit($limit)
                    ->get();
            }
        );
    }

    /**
     * Clear all cache for a site.
     */
    protected function clearSiteCache(\App\Models\Site $site): void
    {
        $siteId = $site->id ?? 0;
        
        // Clear all site-related cache keys
        Cache::tags(['site', 'site_' . $siteId])->flush();
    }

    /**
     * Clear cache for a specific post.
     */
    protected function clearPostCache(\App\Models\Post $post): void
    {
        $siteId = $post->site_id ?? 0;
        $postId = $post->id ?? 0;
        
        // Clear post-related cache
        Cache::tags(['post', 'post_' . $postId, 'site_' . $siteId])->flush();
    }

    /**
     * Clear cache for a specific category or tag.
     */
    protected function clearTaxonomyCache($term): void
    {
        $siteId = $term->site_id ?? 0;
        $termId = $term->id ?? 0;
        
        // Clear taxonomy-related cache
        Cache::tags(['taxonomy', 'term_' . $termId, 'site_' . $siteId])->flush();
    }
}
