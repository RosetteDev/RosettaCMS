<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\SiteSetting;
use App\Facades\CDN;
use Intervention\Image\Facades\Image;

/**
 * Theme helper trait for public rendering.
 */
trait ThemeHelper
{
    /**
     * Get site settings with caching.
     */
    protected function settings(): SiteSetting
    {
        $site = $this->site();
        $key = $site?->id ?? 0;

        return \Cache::remember(
            "site_settings.{$key}",
            300,
            fn () => \App\Models\SiteSetting::forSite($site)
        );
    }

    /**
     * Get CDN URL for a path.
     */
    protected function themeUrl(string $path): string
    {
        return CDN::cdnUrl($path);
    }

    /**
     * Get responsive image HTML.
     */
    protected function responsiveImage(string $path, array $sizes = [1920, 1200, 800, 400], string $alt = ''): string
    {
        $fallback = '<img src="' . $this->themeUrl($path) . '" alt="' . htmlspecialchars($alt, ENT_QUOTES) . '">';

        if (!class_exists(Image::class)) {
            return $fallback;
        }

        try {
            $relative = ltrim($path, '/');
            $originalPath = storage_path('app/public/' . $relative);

            if (!file_exists($originalPath)) {
                return $fallback;
            }

            $info = pathinfo($originalPath);
            $size = @getimagesize($originalPath);
            if (!$size) {
                return $fallback;
            }
            $originalWidth = $size[0];
            $originalHeight = $size[1];

            $srcset = [];

            foreach ($sizes as $targetWidth) {
                if ($targetWidth >= $originalWidth) {
                    continue;
                }
                $resizedPath = $info['dirname'] . '/' . $info['filename'] . '-' . $targetWidth . 'w.' . $info['extension'];
                if (!file_exists($resizedPath)) {
                    continue;
                }
                $resizedRelative = str_replace(storage_path('app/public') . '/', '', $resizedPath);
                $srcset[] = $this->themeUrl('storage/' . $resizedRelative) . ' ' . $targetWidth . 'w';
            }

            // Always include the original as the largest source.
            $srcset[] = $this->themeUrl($path) . ' ' . $originalWidth . 'w';

            $altAttr = htmlspecialchars($alt, ENT_QUOTES);

            return '<img src="' . $this->themeUrl($path) . '" '
                . 'srcset="' . implode(', ', $srcset) . '" '
                . 'sizes="(max-width: 1200px) 100vw, 1200px" '
                . 'width="' . $originalWidth . '" height="' . $originalHeight . '" '
                . 'loading="lazy" decoding="async" '
                . 'alt="' . $altAttr . '">';
        } catch (\Exception $e) {
            return $fallback;
        }
    }

    /**
     * Convert inline styles to classes for consistent theming.
     */
    protected function styleToClass(string $style): string
    {
        return preg_replace_callback('/([a-z-]+):\s*([^;]+)/i', function ($matches) {
            $property = $matches[1];
            $value = trim($matches[2]);

            return $property . '-' . preg_replace('/[^a-z0-9-]/i', '-', strtolower($value));
        }, $style) ?: '';
    }

    /**
     * Get block type styles.
     */
    protected function getBlockStyles(): array
    {
        return [
            'text' => [
                'alignment' => ['left', 'center', 'right'],
                'margin' => ['sm', 'md', 'lg'],
            ],
            'heading' => [
                'level' => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'],
                'alignment' => ['left', 'center', 'right'],
                'margin' => ['sm', 'md', 'lg'],
            ],
            'callout' => [
                'style' => ['primary', 'secondary', 'success', 'danger', 'warning', 'info'],
            ],
        ];
    }
}
