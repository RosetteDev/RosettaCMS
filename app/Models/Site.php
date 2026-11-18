<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

use App\Models\Theme;

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
        'home_post_id',
        'theme',
        'theme_options',
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

    /**
     * Get the homepage post for the site.
     */
    public function homepage()
    {
        return $this->belongsTo(Post::class, 'home_post_id');
    }

    /**
     * Get the theme options for the site.
     */
    public function getThemeOptionsAttribute()
    {
        return json_decode($this->attributes['theme_options'] ?? '[]', true);
    }

    /**
     * Get the active theme for the site.
     */
    public function getActiveThemeAttribute()
    {
        $slug = $this->theme ?? 'rosetta';
        $themeJsonPath = base_path("themes/{$slug}/theme.json");
        
        if (file_exists($themeJsonPath)) {
            $themeData = json_decode(file_get_contents($themeJsonPath), true);
            return array_merge($themeData, [
                'slug' => $slug,
                'options' => $this->theme_options ?? [],
            ]);
        }
        
        return [
            'name' => 'Rosetta',
            'slug' => 'rosetta',
            'version' => '1.0.0',
            'author' => 'Rosetta Dev Team',
            'options' => $this->theme_options ?? [],
        ];
    }
}
