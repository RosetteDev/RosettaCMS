<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageView extends Model
{
    protected $table = 'page_views';

    public $timestamps = false;

    protected $fillable = [
        'site_id',
        'path',
        'title',
        'post_id',
        'referrer',
        'device',
        'browser',
        'visitor_hash',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
