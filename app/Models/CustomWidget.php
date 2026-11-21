<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomWidget extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'content',
        'type',
        'location',
        'context',
        'priority',
        'enabled',
        'user_id',
    ];

    /**
     * Get the user that owns the widget.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include enabled widgets.
     */
    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    /**
     * Get a widget by slug.
     */
    public static function findBySlug(string $slug): ?self
    {
        return self::where('slug', $slug)->first();
    }

    /**
     * Get widgets for a specific location and context.
     */
    public static function forLocation(string $location, string $context = 'normal'): array
    {
        return self::enabled()
            ->where('location', $location)
            ->where('context', $context)
            ->orderBy('priority')
            ->get()
            ->toArray();
    }
}
