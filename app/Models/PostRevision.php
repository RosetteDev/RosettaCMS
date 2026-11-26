<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mews\Purifier\Facades\Purifier;

class PostRevision extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'post_id',
        'user_id',
        'title',
        'content',
        'excerpt',
        'meta',
        'changes',
        'revision_type',
        'revision_number',
        'status',
        'revision_date',
        'revision_date_gmt',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'changes' => 'array',
            'revision_date' => 'datetime',
            'revision_date_gmt' => 'datetime',
        ];
    }

    /**
     * Get the post that owns this revision.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the user that created this revision.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the revision content, sanitized so no view can render untrusted markup.
     */
    public function getContentAttribute($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return Purifier::clean((string) $value, [
            'HTML.Allowed' => 'p,br,strong,b,em,i,u,s,ul,ol,li,a[href|title],div,span,hr,h1,h2,h3,h4,h5,h6,img[src|alt|title|width|height]',
            'Attr.EnableID' => true,
        ]);
    }
}
