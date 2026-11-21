<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

/**
 * Image optimizer trait for media assets.
 */
trait ImageOptimizer
{
    /**
     * Convert image to WebP format.
     */
    protected function convertToWebP(string $filePath): ?string
    {
        if (!extension_loaded('gd') && !extension_loaded('imagick')) {
            return null;
        }

        try {
            $image = Image::make($filePath);

            if (!$image) {
                return null;
            }

            $webpPath = pathinfo($filePath, PATHINFO_DIRNAME) . '/' . pathinfo($filePath, PATHINFO_FILENAME) . '.webp';

            $image->save($webpPath, 80, 'webp');

            return $webpPath;
        } catch (\Exception $e) {
            report($e);
            return null;
        }
    }

    /**
     * Generate responsive image sources.
     */
    protected function generateResponsiveImages(string $filePath, array $sizes = [1920, 1200, 800, 400]): array
    {
        $sources = [];
        $originalWidth = $this->getImageWidth($filePath);

        foreach ($sizes as $size) {
            if ($originalWidth > $size) {
                $resizedPath = $this->generateResizedImage($filePath, $size);

                if ($resizedPath) {
                    $sources[] = [
                        'path' => $resizedPath,
                        'width' => $size,
                        'mime' => $this->getImageMime($resizedPath),
                    ];
                }
            }
        }

        // Always include original as highest quality source
        $sources[] = [
            'path' => $filePath,
            'width' => $originalWidth,
            'mime' => $this->getImageMime($filePath),
        ];

        return $sources;
    }

    /**
     * Generate resized image.
     */
    protected function generateResizedImage(string $filePath, int $maxWidth): ?string
    {
        try {
            $image = Image::make($filePath);

            if (!$image) {
                return null;
            }

            $info = pathinfo($filePath);
            $resizedPath = $info['dirname'] . '/' . $info['filename'] . '-' . $maxWidth . 'w.' . $info['extension'];

            $image->resize($maxWidth, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })->save($resizedPath);

            return $resizedPath;
        } catch (\Exception $e) {
            report($e);
            return null;
        }
    }

    /**
     * Get image width.
     */
    protected function getImageWidth(string $filePath): ?int
    {
        try {
            $image = Image::make($filePath);
            return $image ? $image->width() : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get image MIME type.
     */
    protected function getImageMime(string $filePath): ?string
    {
        try {
            $image = Image::make($filePath);
            return $image ? $image->mime() : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Check if image optimization is available.
     */
    protected function isImageOptimizationAvailable(): bool
    {
        return class_exists(\Intervention\Image\Facades\Image::class);
    }

    /**
     * Optimize image using Intervention Image.
     */
    protected function optimizeImage(string $filePath, int $quality = 80): bool
    {
        if (!$this->isImageOptimizationAvailable()) {
            return false;
        }

        try {
            $image = Image::make($filePath)->encode(null, $quality);
            return (bool) $image->save($filePath);
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }
}
