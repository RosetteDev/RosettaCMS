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
    protected function responsiveImage(string $path, array $sizes = [1920, 1200, 800, 400]): string
    {
        if (!class_exists(Image::class)) {
            return '<img src="' . $this->themeUrl($path) . '" alt="">';
        }

        try {
            $originalPath = storage_path('app/public/' . ltrim($path, '/'));
            
            if (!file_exists($originalPath)) {
                return '<img src="' . $this->themeUrl($path) . '" alt="">';
            }

            $info = pathinfo($originalPath);
            $webpPath = $info['dirname'] . '/' . $info['filename'] . '.webp';

            $sources = [];
            foreach ($sizes as $size) {
                $resizedPath = $info['dirname'] . '/' . $info['filename'] . '-' . $size . 'w.' . $info['extension'];
                
                if (file_exists($resizedPath)) {
                    $sources[] = [
                        'path' => $resizedPath,
                        'width' => $size,
                    ];
                }
            }

            $originalWidth = getimagesize($originalPath)[0] ?? 1920;
            $sources[] = [
                'path' => $originalPath,
                'width' => $originalWidth,
            ];

            $urls = array_map(function ($source) {
                return $this->themeUrl('storage/' . basename($source['path']) . ' ' . $source['width'] . 'w');
            }, $sources);

            return '<img src="' . $this->themeUrl($path) . '" 
                    srcset="' . implode(', ', $urls) . '"
                    sizes="(max-width: 1200px) 100vw, 1200px"
                    alt="">';
        } catch (\Exception $e) {
            return '<img src="' . $this->themeUrl($path) . '" alt="">';
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
