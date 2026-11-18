<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Comment extends Model
{
    protected $fillable = [
        'post_id',
        'user_id',
        'parent_id',
        'content',
        'author_name',
        'author_email',
        'author_url',
        'status',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Get the post that this comment belongs to.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the user that authored this comment (if registered).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent comment (for replies).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Get all replies to this comment.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    /**
     * Scope a query to only include approved comments.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include pending comments.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include spam comments.
     */
    public function scopeSpam($query)
    {
        return $query->where('status', 'spam');
    }

    /**
     * Scope a query to only include rejected comments.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Check if the comment is from a guest (no user).
     */
    public function isFromGuest(): bool
    {
        return $this->user_id === null;
    }
}
