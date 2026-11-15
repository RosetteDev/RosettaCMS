<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'user_id',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
        'alt_text',
        'caption',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'file_size' => 'integer',
    ];

    /**
     * Get the site that owns the media.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the user that owns the media.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
