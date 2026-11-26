<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LanguageContent extends Model
{
    protected $table = 'language_content';

    protected $fillable = [
        'content_type',
        'content_id',
        'language_id',
        'title',
        'content',
        'slug',
        'excerpt',
        'meta',
        'translated_by',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    /**
     * The language this translation belongs to.
     */
    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    /**
     * The user who performed the translation.
     */
    public function translator()
    {
        return $this->belongsTo(User::class, 'translated_by');
    }

    /**
     * The original content this translation belongs to.
     */
    public function content()
    {
        return $this->belongsTo(Post::class, 'content_id');
    }
}
