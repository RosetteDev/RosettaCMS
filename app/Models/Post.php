<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'user_id',
        'title',
        'slug',
        'content',
        'blocks',
        'meta',
        'post_type',
        'status',
        'published_at',
        'comment_status',
    ];

    protected $casts = [
        'blocks' => 'array',
        'meta' => 'array',
        'published_at' => 'datetime',
    ];

    /**
     * Get the site that owns the post.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the user that owns the post.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the author of the post (alias for user).
     */
    public function author()
    {
        return $this->user();
    }

    /**
     * Get the terms for the post.
     */
    public function terms()
    {
        return $this->belongsToMany(Term::class);
    }

    /**
     * Get the taxonomies associated with the post.
     */
    public function taxonomies()
    {
        return $this->belongsToMany(Taxonomy::class, 'post_term');
    }

    /**
     * Get the categories associated with the post.
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_post');
    }

    /**
     * Get the tags associated with the post.
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'tag_post');
    }

    /**
     * Get the media associated with the post.
     */
    public function media()
    {
        return $this->hasMany(Media::class);
    }

    /**
     * Scope a query to only include published posts.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->whereNull('published_at')
            ->orWhere('published_at', '<=', now());
    }

    /**
     * Scope a query to only include posts of a specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('post_type', $type);
    }
}
