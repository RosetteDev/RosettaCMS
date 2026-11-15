<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
