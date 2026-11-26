<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Backup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'disk',
        'path',
        'size_bytes',
        'tables_count',
        'media_count',
        'manifest',
        'created_by',
    ];

    protected $casts = [
        'manifest' => 'array',
        'size_bytes' => 'integer',
        'tables_count' => 'integer',
        'media_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Human readable size, e.g. "1.4 MB".
     */
    public function getHumanSizeAttribute(): string
    {
        $bytes = (int) $this->size_bytes;

        foreach (['B', 'KB', 'MB', 'GB'] as $unit) {
            if ($bytes < 1024) {
                return round($bytes, $unit === 'B' ? 0 : 1) . ' ' . $unit;
            }

            $bytes /= 1024;
        }

        return round($bytes, 1) . ' TB';
    }
}
