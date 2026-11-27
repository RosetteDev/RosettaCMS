<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'user_id',
        'file_name',
        'file_path',
        'folder',
        'mime_type',
        'file_size',
        'alt_text',
        'caption',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'file_size' => 'integer',
    ];

    /**
     * Get the public URL for the file.
     */
    public function getUrlAttribute(): string
    {
        $path = ltrim((string) $this->file_path, '/');

        if (\App\Facades\CDN::isCDNEnabled()) {
            return \App\Facades\CDN::cdnUrl('storage/' . $path);
        }

        return asset('storage/' . $path);
    }

    /**
     * Determine if the file is an image.
     */
    public function getIsImageAttribute(): bool
    {
        return str_starts_with((string) $this->mime_type, 'image/');
    }

    /**
     * Human readable file size.
     */
    public function getHumanSizeAttribute(): string
    {
        $bytes = (int) $this->file_size;

        foreach (['B', 'KB', 'MB', 'GB'] as $unit) {
            if ($bytes < 1024) {
                return round($bytes, $unit === 'B' ? 0 : 1) . ' ' . $unit;
            }

            $bytes /= 1024;
        }

        return round($bytes, 1) . ' TB';
    }

    /**
     * Get the site that owns the media.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the user that owns the media.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Fire a `media.created` webhook when a file is uploaded.
     */
    protected static function booted(): void
    {
        static::created(function (Media $media) {
            try {
                app(\App\Services\WebhookService::class)->dispatch(
                    'media.created',
                    [
                        'id' => $media->id,
                        'file_name' => $media->file_name,
                        'url' => $media->url,
                        'mime_type' => $media->mime_type,
                        'file_size' => $media->file_size,
                        'folder' => $media->folder,
                    ],
                    $media->site_id
                );
            } catch (\Throwable $e) {
                report($e);
            }
        });
    }
}
