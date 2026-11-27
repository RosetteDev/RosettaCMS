<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

/**
 * CDN helper trait for media assets.
 */
trait CDNHelper
{
    /**
     * Check if CDN is enabled.
     */
    public function isCDNEnabled(): bool
    {
        return (bool) env('CDN_ENABLED', false);
    }

    /**
     * Get CDN URL.
     */
    public function getCDNUrl(): ?string
    {
        if (!$this->isCDNEnabled()) {
            return null;
        }

        return env('CDN_URL');
    }

    /**
     * Get CDN-enabled URL for a file.
     */
    public function cdnUrl(string $path): string
    {
        $cdnUrl = $this->getCDNUrl();

        if ($cdnUrl) {
            // Remove leading slash from path if present
            $path = ltrim($path, '/');

            // If CDN URL doesn't end with slash and path doesn't start with slash, add one
            if (!str_ends_with($cdnUrl, '/')) {
                return $cdnUrl . '/' . $path;
            }

            return $cdnUrl . $path;
        }

        // Return local URL
        return asset($path);
    }

    /**
     * Get CDN-enabled URL for storage disk.
     */
    public function cdnStorageUrl(string $path, string $disk = 'public'): string
    {
        if ($this->isCDNEnabled()) {
            return $this->cdnUrl($path);
        }

        return Storage::disk($disk)->url($path);
    }

    /**
     * Check if CDN is configured properly.
     */
    public function isCDNConfigured(): bool
    {
        if (!$this->isCDNEnabled()) {
            return true;
        }

        return (bool) env('CDN_URL');
    }
}
