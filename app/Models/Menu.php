<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'name',
        'slug',
        'location',
    ];

    protected static function booted(): void
    {
        static::creating(function (Menu $menu) {
            if (empty($menu->slug)) {
                $menu->slug = Str::slug($menu->name);
            }
        });
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function items()
    {
        return $this->hasMany(MenuItem::class)->orderBy('order');
    }

    public function rootItems()
    {
        return $this->hasMany(MenuItem::class)->whereNull('parent_id')->orderBy('order');
    }

    /**
     * Get the menu assigned to a location for the current (or given) site.
     */
    public static function forLocation(string $location = 'header', ?Site $site = null): ?self
    {
        $site = $site ?: Site::first();

        if (!$site) {
            return null;
        }

        return static::where('site_id', $site->id)
            ->where('location', $location)
            ->with(['rootItems.children'])
            ->first();
    }
}
