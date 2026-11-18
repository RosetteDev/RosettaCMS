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
        'excerpt',
        'featured_image',
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
     * Get the comments associated with the post.
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the count of comments for the post.
     */
    public function getCommentsCountAttribute()
    {
        return $this->comments()->count();
    }

    /**
     * Get the count of approved comments for the post.
     */
    public function getApprovedCommentsCountAttribute()
    {
        return $this->comments()->where('status', 'approved')->count();
    }

    /**
     * Get approved comments for the post.
     */
    public function approvedComments()
    {
        return $this->hasMany(Comment::class)->where('status', 'approved');
    }

    /**
     * Scope a query to only include published posts.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where(function ($query) {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    /**
     * Render the post content as HTML, converting Markdown when the stored
     * value is not already HTML.
     */
    public function getRenderedContentAttribute(): string
    {
        $content = (string) $this->content;

        if ($content === '') {
            return '';
        }

        if (preg_match('/<\/[a-z][^>]*>/i', $content)) {
            return $content;
        }

        $converter = new \League\CommonMark\CommonMarkConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        return (string) $converter->convert($content);
    }

    /**
     * Get a plain-text excerpt for listings and meta descriptions.
     */
    public function getPlainExcerptAttribute(): string
    {
        if ($this->excerpt) {
            return trim(strip_tags($this->excerpt));
        }

        return trim(preg_replace('/\s+/', ' ', strip_tags($this->rendered_content)));
    }

    /**
     * Resolve the featured image to a usable URL.
     *
     * Absolute URLs are returned untouched; relative paths are served from the
     * public storage disk.
     */
    public function getFeaturedImageUrlAttribute(): ?string
    {
        $image = $this->featured_image;

        if (!$image) {
            return null;
        }

        if (preg_match('#^(https?:)?//#i', $image)) {
            return $image;
        }

        return asset('storage/' . ltrim($image, '/'));
    }

    /**
     * Get the primary category for the post.
     */
    public function getPrimaryCategoryAttribute()
    {
        return $this->categories->first();
    }

    /**
     * Get the public URL for the post.
     */
    public function getPublicUrlAttribute(): string
    {
        return $this->post_type === 'page'
            ? route('page.show', $this->slug)
            : route('post.show', $this->slug);
    }

    /**
     * Scope a query to only include posts of a specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('post_type', $type);
    }
}
