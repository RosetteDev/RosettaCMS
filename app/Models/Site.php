<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Site extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'description',
        'timezone',
        'locale',
        'currency',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Boot method to register model events
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($site) {
            if (empty($site->slug)) {
                $site->slug = Str::slug($site->name);
            }
        });
    }

    /**
     * Get the posts for the site.
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Get the media for the site.
     */
    public function media()
    {
        return $this->hasMany(Media::class);
    }

    /**
     * Get the taxonomies for the site.
     */
    public function taxonomies()
    {
        return $this->hasMany(Taxonomy::class);
    }

    /**
     * Get the settings for the site.
     */
    public function settings()
    {
        return $this->hasOne(SiteSetting::class);
    }

    /**
     * Get the users for the site.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'site_user')->withTimestamps();
    }
}
