<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Mews\Purifier\Facades\Purifier;

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
        'meta',
        'post_type',
        'status',
        'published_at',
        'comment_status',
    ];

    protected $casts = [
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
     * Get the blocks for the post.
     */
    public function blocks()
    {
        return $this->hasMany(Block::class)->orderBy('order');
    }

    /**
     * Get the menu items that reference this post.
     */
    public function menuItems()
    {
        return $this->hasMany(MenuItem::class, 'page_id');
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
     * Determine whether the post is scheduled to go live in the future.
     */
    public function isScheduled(): bool
    {
        return $this->published_at !== null
            && $this->published_at->isFuture()
            && in_array($this->status, ['pending', 'published'], true);
    }

    /**
     * Scope a query to only include scheduled posts (pending publication).
     */
    public function scopeScheduled($query)
    {
        return $query->whereIn('status', ['pending', 'published'])
            ->where('published_at', '>', now());
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

        if (!preg_match('/<\/[a-z][^>]*>/i', $content)) {
            $converter = new \League\CommonMark\CommonMarkConverter([
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
            ]);

            $content = (string) $converter->convert($content);
        }

        // Always sanitize before output, whether the stored value was Markdown
        // or raw HTML, so no view can render untrusted markup.
        return $this->sanitize($content);
    }

    /**
     * Sanitize HTML content to prevent XSS attacks.
     */
    protected function sanitize(string $content): string
    {
        $allowed = implode(',', [
            'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'strike',
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'ul', 'ol', 'li',
            'a[href|title|target|rel]',
            'img[src|alt|title|width|height]',
            'blockquote', 'code', 'pre',
            'table', 'thead', 'tbody', 'tr', 'th', 'td',
            'div', 'span', 'hr', 'figure', 'figcaption',
            'iframe[src|width|height|frameborder|allowfullscreen]',
        ]);

        return Purifier::clean($content, [
            'HTML.Allowed' => $allowed,
            'Attr.EnableID' => true,
            'HTML.SafeIframe' => true,
            'URI.SafeIframeRegexp' => '%^https://(www\.)?(youtube\.com/embed/|player\.vimeo\.com/video/)%',
        ]);
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

    /**
     * Fire webhook events when a post/page is created or updated.
     *
     * - Created while published, or transitioned to published on update,
     *   fires `{type}.published`.
     * - Any other save fires `{type}.updated`.
     *
     * Drafts created and saved are deliberately silent — the event stream
     * tracks publishing, not keystrokes.
     */
    protected static function booted(): void
    {
        static::created(function (Post $post) {
            if ($post->status === 'published') {
                static::dispatchWebhook($post, 'published');
            }
        });

        static::updated(function (Post $post) {
            $wasPublished = $post->getOriginal('status') === 'published';

            if ($post->status === 'published' && !$wasPublished) {
                static::dispatchWebhook($post, 'published');
                return;
            }

            if ($post->wasChanged()) {
                static::dispatchWebhook($post, 'updated');
            }
        });
    }

    /**
     * Dispatch the post/page webhook event.
     */
    protected static function dispatchWebhook(Post $post, string $action): void
    {
        $type = $post->post_type === 'page' ? 'page' : 'post';
        $path = $type === 'page' ? 'pages' : 'posts';

        try {
            app(\App\Services\WebhookService::class)->dispatch(
                $type . '.' . $action,
                [
                    'type' => $type,
                    'id' => $post->id,
                    'slug' => $post->slug,
                    'title' => $post->title,
                    'status' => $post->status,
                    'url' => url('/' . $path . '/' . $post->slug),
                    'published_at' => $post->published_at?->toIso8601String(),
                ],
                $post->site_id
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
