<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    use HasFactory;

    protected $table = 'redirects';

    protected $fillable = [
        'site_id',
        'from_path',
        'to_url',
        'type',
        'hit_count',
        'is_active',
    ];

    protected $casts = [
        'hit_count' => 'integer',
        'is_active' => 'boolean',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Active redirects for a site, keyed by normalized path.
     */
    public static function forSite(Site $site)
    {
        return static::where('site_id', $site->id)
            ->where('is_active', true)
            ->get()
            ->keyBy(fn ($redirect) => self::normalize($redirect->from_path));
    }

    /**
     * Normalize a from_path so "/foo", "foo/" and "foo" all match.
     */
    public static function normalize(string $path): string
    {
        return trim($path, '/');
    }

    public function isPermanent(): bool
    {
        return $this->type === '301';
    }
}
